import $ from 'jquery';
import { Instance as gpm } from '../utils/gpm';
import { translations } from 'lev-config';
import toastr from '../utils/toastr';

// Check for updates trigger
$('[data-gpm-checkupdates]').on('click', function() {
    let element = $(this);
    element.find('i').addClass('fa-spin');

    gpm.fetch((response) => {
        element.find('i').removeClass('fa-spin');
        let payload = response.payload;

        if (!payload) { return; }
        if (!payload.lev.isUpdatable && !payload.resources.total) {
            toastr.success(translations.PLUGIN_ADMIN.EVERYTHING_UP_TO_DATE);
        } else {
            var lev = payload.lev.isUpdatable ? 'Lev v' + payload.lev.available : '';
            var resources = payload.resources.total ? payload.resources.total + ' ' + translations.PLUGIN_ADMIN.UPDATES_ARE_AVAILABLE : '';

            if (!resources) { lev += ' ' + translations.PLUGIN_ADMIN.IS_AVAILABLE_FOR_UPDATE; }
            toastr.info(lev + (lev && resources ? ' ' + translations.PLUGIN_ADMIN.AND + ' ' : '') + resources);
        }
    }, true);
});
