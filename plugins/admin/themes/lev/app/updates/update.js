import $ from 'jquery';
import { translations } from 'lev-config';
import formatBytes from '../utils/formatbytes';
import request from '../utils/request';
import { Instance as Update } from './index';

// Dashboard update and Lev update
$(document).on('click.remodal', '[data-remodal-id="update-lev"] [data-remodal-action="confirm"]', () => {
    const element = $('#lev-update-button');
    element.html(`${translations.PLUGIN_ADMIN.UPDATING_PLEASE_WAIT} ${formatBytes(Update.payload.lev.assets['lev-update'].size)}..`);

    element.attr('disabled', 'disabled').find('> .fa').removeClass('fa-cloud-download').addClass('fa-refresh fa-spin');

    request(Update.updateURL, (response) => {
        if (response.type === 'updatelev') {
            $('[data-gpm-lev]').remove();
            $('#footer .lev-version').html(response.version);
        }

        element.removeAttr('disabled').find('> .fa').removeClass('fa-refresh fa-spin').addClass('fa-cloud-download');
    });
});
