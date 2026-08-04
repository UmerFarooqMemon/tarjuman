/**
 * Site settings image galleries: + tile adds files, delete removes keep/pending.
 */
'use strict';

(function () {
  function createPendingTile(file, index, onRemove) {
    var tile = document.createElement('div');
    tile.className = 'settings-image-tile';
    tile.setAttribute('data-pending', String(index));

    var img = document.createElement('img');
    img.alt = file.name || '';
    img.src = URL.createObjectURL(file);
    tile.appendChild(img);

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'settings-image-tile__delete';
    btn.setAttribute('data-gallery-remove', '');
    btn.setAttribute('aria-label', 'Delete');
    btn.innerHTML = '<i class="ti ti-x"></i>';
    btn.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      URL.revokeObjectURL(img.src);
      onRemove(index);
    });
    tile.appendChild(btn);

    return tile;
  }

  function syncFileInput(input, files) {
    var dt = new DataTransfer();
    files.forEach(function (file) {
      dt.items.add(file);
    });
    input.files = dt.files;
  }

  function initGallery(root) {
    var grid = root.querySelector('[data-gallery-grid]');
    var picker = root.querySelector('[data-gallery-picker]');
    var fileInput = root.querySelector('[data-gallery-files]');
    var addTile = root.querySelector('[data-gallery-add]');

    if (!grid || !picker || !fileInput || !addTile) {
      return;
    }

    var pending = [];

    function renderPending() {
      grid.querySelectorAll('[data-pending]').forEach(function (node) {
        node.remove();
      });

      pending.forEach(function (file, index) {
        var tile = createPendingTile(file, index, function (removeIndex) {
          pending.splice(removeIndex, 1);
          syncFileInput(fileInput, pending);
          renderPending();
        });
        grid.insertBefore(tile, addTile);
      });

      syncFileInput(fileInput, pending);
    }

    picker.addEventListener('change', function () {
      var selected = Array.prototype.slice.call(picker.files || []);
      selected.forEach(function (file) {
        if (file && file.type && file.type.indexOf('image/') === 0) {
          pending.push(file);
        }
      });
      picker.value = '';
      renderPending();
    });

    grid.addEventListener('click', function (event) {
      var btn = event.target.closest('[data-gallery-remove]');
      if (!btn) {
        return;
      }

      var tile = btn.closest('.settings-image-tile');
      if (!tile || !tile.hasAttribute('data-existing')) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();
      tile.remove();
    });
  }

  document.querySelectorAll('[data-gallery]').forEach(initGallery);
})();
