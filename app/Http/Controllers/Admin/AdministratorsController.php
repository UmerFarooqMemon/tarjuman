<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreAdministartorRequest;
use App\Http\Requests\Admin\UpdateAdministartorRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class AdministratorsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->registerCrudPermissions('administrators');
        $this->middleware('permission:administrators.edit')->only(['changeStatus']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Admin::with('roles')->get();

        return view('admin.administrators.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = $this->availableRoles();

        return view('admin.administrators.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAdministartorRequest $request)
    {
        $data = $request->except([
            '_token',
            '_method',
            'image',
            'role',
            'password_confirmation',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = 'admin-profile-'.time().'.'.$extension;
            $file->move(uploadsDir('admin'), $filename);
            $data['image'] = $filename;
        }

        $password = $request->password;
        $data['password'] = bcrypt($password);
        $data['is_active'] = 1;

        if ($data['email'] != '') {
            Mail::send(
                'emails.admin.created',
                [
                    'data' => $data,
                    'password' => $password,
                ],
                function ($message) use ($data) {
                    $email = $data['email'];
                    $message->to($email, $email);
                    $message->replyTo(config('mail.from.address'), config('mail.from.name'));
                    $subject = 'Account created.';
                    $message->subject($subject);
                }
            );
        }

        $admin = Admin::create($data);
        $admin->syncRoles([$request->role]);

        return redirect()
            ->route('admin.administrators.index')
            ->with('success', __('general.user_has_been_created_successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Admin::with('roles')->findOrFail($id);

        return view('admin.administrators.show', compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = Admin::with('roles')->findOrFail($id);
        $authAdmin = auth('admin')->user();

        if ($authAdmin->is_system_admin || $authAdmin->id == $data->id || ! $data->is_system_admin) {
            $roles = $this->availableRoles();

            return view('admin.administrators.edit', compact('data', 'roles'));
        }

        return redirect()
            ->route('admin.administrators.index')
            ->with('error', __('general.you_cannot_change_other_admin_details'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAdministartorRequest $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $authAdmin = auth('admin')->user();

        if (! ($authAdmin->is_system_admin || $authAdmin->id == $admin->id || ! $admin->is_system_admin)) {
            return redirect()
                ->route('admin.administrators.index')
                ->with('error', __('general.you_cannot_change_other_admin_details'));
        }

        $data = $request->except([
            '_token',
            '_method',
            'email',
            'previous_image',
            'image',
            'role',
            'password',
            'password_confirmation',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = 'admin-profile-'.time().'.'.$extension;
            $file->move(uploadsDir('admin'), $filename);

            if ($request->previous_image != '' && file_exists(uploadsDir('admin').$request->previous_image)) {
                unlink(uploadsDir('admin').$request->previous_image);
            }

            $data['image'] = $filename;
        }

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $admin->update($data);

        if ($request->filled('role') && ! $admin->is_system_admin) {
            $admin->syncRoles([$request->role]);
        }

        return redirect()
            ->route('admin.administrators.index')
            ->with('success', __('general.administrator_has_been_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Admin::findOrFail($id);
        $authAdmin = auth('admin')->user();

        if ($authAdmin->id == $id || $data->is_system_admin) {
            return redirect()
                ->route('admin.administrators.index')
                ->with('error', __('general.you_can_not_delete_admin'));
        }

        if ($data->image != '' && file_exists(uploadsDir('admin').$data->image)) {
            unlink(uploadsDir('admin').$data->image);
        }

        $data->roles()->detach();
        $data->delete();

        return redirect()
            ->route('admin.administrators.index')
            ->with('success', __('general.administrator_was_removed_successfully'));
    }

    public function EditProfile()
    {
        $data = Admin::findOrFail(auth('admin')->id());

        return view('admin.profile-update', compact('data'));
    }

    public function updateProfile(\App\Http\Requests\Admin\UpdateProfileRequest $request)
    {
        $admin = Admin::findOrFail(auth('admin')->id());

        $data = $request->except([
            '_token',
            '_method',
            'email',
            'previous_image',
            'image',
            'password',
            'password_confirmation',
            'role',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = 'admin-profile-'.time().'.'.$extension;
            $file->move(uploadsDir('admin'), $filename);

            if ($request->previous_image != '' && file_exists(uploadsDir('admin').$request->previous_image)) {
                unlink(uploadsDir('admin').$request->previous_image);
            }

            $data['image'] = $filename;
        }

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $admin->update($data);

        return redirect()
            ->route('admin.update-profile')
            ->with('success', __('general.administrator_has_been_updated_successfully'));
    }

    public function changeStatus(Request $request)
    {
        if (isset($request->id) && isset($request->status)) {
            $admin = Admin::find($request->id);

            if (! $admin || $admin->is_system_admin) {
                return response()->json([
                    'error' => 1,
                    'message' => __('general.something_went_wrong_please_try_again_later'),
                    'data' => [],
                ]);
            }

            $admin->update(['is_active' => $request->status]);

            return response()->json([
                'error' => 0,
                'message' => __('general.status_has_been_changed_successfully'),
                'data' => [],
            ]);
        }

        return response()->json([
            'error' => 1,
            'message' => __('general.something_went_wrong_please_try_again_later'),
            'data' => [],
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role>
     */
    protected function availableRoles()
    {
        return Role::where('guard_name', config('admin_permissions.guard', 'admin'))
            ->orderBy('name')
            ->get();
    }
}
