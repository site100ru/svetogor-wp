(function ($bs) {
    $bs.Dropdown.prototype.toggle = function (_orginal) {
        return function () {
            document.querySelectorAll('.drd-submenu').forEach(function (e) {
                e.classList.remove('drd-submenu');
            });
            let drd = this._element.closest('.dropdown').parentNode.closest('.dropdown');
            for (; drd && drd !== document; drd = drd.parentNode.closest('.dropdown')) {
                drd.classList.add('drd-submenu');
            }
            return _orginal.call(this);
        }
    }($bs.Dropdown.prototype.toggle);

    document.querySelectorAll('.dropdown').forEach(function (drd) {
        drd.addEventListener('hide.bs.dropdown', function (e) {
            if (this.classList.contains('drd-submenu')) {
                this.classList.remove('drd-submenu');
                e.preventDefault();
            }
            e.stopPropagation();
        });
    });
})(bootstrap);
