<?php


/**
 * Клас 'ztm_Adapter'
 *
 * Табло с настройки за състояния
 *
 *
 * @author    Nevena Georgieva <nevena.georgieva89@gmail.com>
 * @copyright 2006 - 2019 Experta OOD
 * @license   GPL 3
 *
 * @since     v 0.1
 */
class ztm_Adapter extends core_Mvc
{
    public function act_Default()
    {
        Mode::set('wrapper', 'page_PureHtml');


        $tpl = new ET(getTplFromFile('ztm/tpl/dashboard.shtml'));


        $tpl->push('ztm/css/bootstrap.min.css', 'CSS');
        $tpl->push('ztm/css/bootstrap-grid.css', 'CSS');
        $tpl->push('ztm/css/font-awesome.min.css', 'CSS');
        $tpl->push('ztm/css/rangeslider.css', 'CSS');
        $tpl->push('ztm/css/layout.css', 'CSS');


        $tpl->push('ztm/js/jquery-3.1.1.min.js', 'JS');
        $tpl->push('ztm/js/popper.min.js', 'JS');
        $tpl->push('ztm/js/bootstrap.min.js', 'JS');
        $tpl->push('ztm/js/skycons.js', 'JS');
        $tpl->push('ztm/js/rangeslider.js', 'JS');
        $tpl->push('ztm/js/custom.js', 'JS');


        $now = dt::now(false);
        $forRec = darksky_Forecasts::getForecast($now);
        if ($forRec) {
            $iconUrl = 'https://darksky.net/images/weather-icons/' . $forRec->icon . '.png';

            $data = json_encode($forRec);
            jquery_Jquery::run($tpl, "prepareDashboard({$data})");

        }

        return $tpl;
    }
}
