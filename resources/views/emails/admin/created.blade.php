<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
    </head>
    <body>
        <div style="background-color: #f4f4f4; padding:10px; margin:auto; font-family: Arial, Helvetica, sans-serif; line-height:30px; ">
                @if ($siteSettings->locale_logo && file_exists(uploadsDir('front') . $siteSettings->locale_logo))
                <img src="{!! asset(uploadsDir('front') . $siteSettings->locale_logo) !!}" cwidth="180" style="margin:20px auto;">
                @else
                <img src="{!! asset('assets/img/logo-2.svg') !!}" width="180" style="margin:20px auto;">
                @endif

                <div style="background: #ffffff; margin:10px; padding:10px;">
                   <p><b>Hi {!! isset($data['first_name']) ? $data['first_name'] : $data['name'] !!},</b></p>
                    <p>Your account was created, credentials given below.</p>

                    <p>
                        <b>Account Type:</b> {!! isset($type) ? $type : 'Administrator' !!}
                        <br />
                        <b>Email Address:</b> {{ $data['email'] }}
                        <br />
                        <b>Password:</b> {{ $password }}
                    </p>

                    <p>Thanks,</p>

                    <p>
                        <b>Best Regards,</b>
                        <br />
                        {{ config('app.name') }}
                    </p>

                </div>
            </div>
    </body>
</html>