import $ from 'jquery';
import unique from 'mout/array/unique';
import { config, translations } from 'lev-config';
import { Instance as gpm } from '../utils/gpm';
import Notifications from './notifications';

import Feed from './feed';
import './check';
import './update';
import './channel-switcher';

export default class Updates {
    constructor(payload = {}) {
        this.setPayload(payload);
        this.task = `task${config.param_sep}`;
        this.updateURL = '';
    }

    setPayload(payload = {}) {
        this.payload = payload;

        return this;
    }

    fetch(force = false) {
        gpm.fetch((response) => this.setPayload(response), force);

        return this;
    }

    maintenance(mode = 'hide') {
        let element = $('#updates [data-update-packages]');

        element[mode === 'show' ? 'fadeIn' : 'fadeOut']();

        if (mode === 'hide') {
            $('.badges.with-updates').removeClass('with-updates').find('.badge.updates').remove();
        }

        return this;
    }

    lev() {
        let payload = this.payload.lev;

        if (payload && payload.isUpdatable) {
            let task = this.task;
            let bar = '';

            if (!payload.isSymlink) {
                this.updateURL = `${config.base_url_relative}/update.json/${task}updatelev/admin-nonce${config.param_sep}${config.admin_nonce}`;
                bar += `<button data-remodal-target="update-lev" class="button button-small secondary pointer-events-none" id="lev-update-button">${translations.PLUGIN_ADMIN.UPDATE_LEV_NOW} <span class="cnt-down">(5s)</span></button>`;
            } else {
                bar += `<span class="hint--left" style="float: right;" data-hint="${translations.PLUGIN_ADMIN.LEV_SYMBOLICALLY_LINKED}"><i class="fa fa-fw fa-link"></i></span>`;
            }

            bar += `
                Lev <b>v${payload.available}</b> ${translations.PLUGIN_ADMIN.IS_NOW_AVAILABLE}! <span class="less">(${translations.PLUGIN_ADMIN.CURRENT} v${payload.version})</span>
            `;

            let element = $('[data-gpm-lev]').removeClass('hidden');

            if (element.is(':empty')) {
                element.hide();
            }

            element
                .addClass('lev')
                .html(`${bar}`)
                .slideDown(150, function() {
                    var c = 5;
                    var x = setInterval(function() {
                        c -= 1;
                        element.find('.pointer-events-none .cnt-down').text('(' + c + 's)');
                    }, 1000);

                    setTimeout(function() {
                        clearInterval(x);
                        element.find('.pointer-events-none .cnt-down').remove();
                        element.find('.pointer-events-none').removeClass('pointer-events-none');
                    }, 5000);
                })
                .parent('#messages').addClass('default-box-shadow');
        }

        return this;
    }

    resources() {
        if (!this.payload || !this.payload.resources || !this.payload.resources.total) {
            return this.maintenance('hide');
        }

        let is_current_package_latest = true;
        let map = ['plugins', 'themes'];
        let singles = ['plugin', 'theme'];
        let { plugins, themes } = this.payload.resources;

        if (!this.payload.resources.total) { return this; }

        [plugins, themes].forEach(function(resources, index) {
            if (!resources || Array.isArray(resources)) { return; }
            let length = Object.keys(resources).length;
            let type = map[index];

            // sidebar
            $(`#admin-menu a[href$="/${map[index]}"]`)
                .find('.badges')
                .addClass('with-updates')
                .find('.badge.updates').text(length);

            var type_translation = '';
            // update all

            if (type === 'plugins') {
                type_translation = translations.PLUGIN_ADMIN.PLUGINS;
            } else {
                type_translation = translations.PLUGIN_ADMIN.THEMES;
            }

            let updateAll = $(`.lev-update.${type}`);
            updateAll.css('display', 'block').html(`
            <p>
                <a href="#" class="button button-small secondary" data-remodal-target="update-packages" data-packages-slugs="${Object.keys(resources).join()}" data-${singles[index]}-action="start-packages-update">${translations.PLUGIN_ADMIN.UPDATE} ${translations.PLUGIN_ADMIN.ALL} ${type_translation}</a>
                <i class="fa fa-bullhorn"></i>
                ${length} ${translations.PLUGIN_ADMIN.OF_YOUR} ${type_translation.toLowerCase()} ${translations.PLUGIN_ADMIN.HAVE_AN_UPDATE_AVAILABLE}
            </p>
            `);

            let existing_slugs = $('[data-update-packages]').attr('data-packages-slugs') || '';

            if (existing_slugs) {
                existing_slugs = existing_slugs.split(',');
            } else {
                existing_slugs = [];
            }

            let slugs = unique(existing_slugs.concat(Object.keys(resources))).join();
            $('[data-update-packages]').attr('data-packages-slugs', `${slugs}`);

            Object.keys(resources).forEach(function(item) {
                // listing page
                let container = $(`[data-gpm-${singles[index]}="${item}"]`);
                let element = container.find('.gpm-name');
                let url = element.find('a');
                let content_wrapper = container.parents('.content-wrapper');

                if (type === 'plugins' && !element.find('.badge.update').length) {
                    element.append(`<a class="plugin-update-button" href="${url.attr('href')}"><span class="badge update">${translations.PLUGIN_ADMIN.UPDATE_AVAILABLE}!</span></a>`);
                    content_wrapper.addClass('has-updates');
                } else if (type === 'themes') {
                    element.append(`<div class="gpm-ribbon"><a href="${url.attr('href')}">${translations.PLUGIN_ADMIN.UPDATE.toUpperCase()}</a></div>`);
                    content_wrapper.addClass('has-updates');
                }

                // details page
                if (container.length) {
                    let details = $(`.lev-update.${singles[index]}`);
                    if (details.length) {
                        let releaseType = resources[item].type === 'testing' ? '<span class="gpm-testing">test release</span>' : '';
                        details.html(`
                            <p>
                                <a href="#" class="button button-small secondary" data-remodal-target="update-packages" data-packages-slugs="${item}" data-${singles[index]}-action="start-package-installation">${translations.PLUGIN_ADMIN.UPDATE} ${singles[index].charAt(0).toUpperCase() + singles[index].substr(1).toLowerCase()}</a>
                                <i class="fa fa-bullhorn"></i>
                                <strong>v${resources[item].available}</strong> ${releaseType} ${translations.PLUGIN_ADMIN.OF_THIS} ${singles[index]} ${translations.PLUGIN_ADMIN.IS_NOW_AVAILABLE}!
                            </p>
                        `).css('display', 'block');

                        is_current_package_latest = false;
                    }
                }
            });

            $('[data-update-packages]').removeClass('hidden');
        });

        $('.content-wrapper').addClass('updates-checked');

        if (!is_current_package_latest) {
            $('.warning-reinstall-not-latest-release').removeClass('hidden');
        }
    }
}

let Instance = new Updates();
export { Instance, Notifications, Feed };

// automatically refresh UI for updates (graph, sidebar, plugin/themes pages) after every fetch
gpm.on('fetched', (response, raw) => {
    Instance.setPayload(response.payload || {});
    Instance.lev().resources();
});

if (config.enable_auto_updates_check === '1') {
    gpm.fetch();
}
