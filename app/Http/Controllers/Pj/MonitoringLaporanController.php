<?php

namespace App\Http\Controllers\Pj;

use App\Http\Controllers\Admin\MonitoringLaporanController as AdminMonitoringLaporanController;

class MonitoringLaporanController extends AdminMonitoringLaporanController
{
    protected function routeName(): string
    {
        return 'pj.monitoring-laporan';
    }

    protected function exportRouteName(): string
    {
        return 'pj.monitoring-laporan.export';
    }

    protected function showRouteName(): string
    {
        return 'pj.monitoring-laporan.show';
    }

    protected function viewTitle(): string
    {
        return 'Monitoring Laporan Kegiatan | Puskesmas Bunar';
    }
}
