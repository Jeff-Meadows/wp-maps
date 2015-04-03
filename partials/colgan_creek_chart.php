<?php
require_once 'WatershedMonitoringChart.php';

class ColganCreekWatershedMonitoringChart extends WatershedMonitoringChart {
    public function __construct() {
        $this->colgan = true;
        $this->sample_type = 'colgan_sample';
        parent::__construct();
    }

    protected function output_extra_chart_options() {
    ?>
        if (name === 'water_temperature') {
            chartOptions.yAxis.plotLines = [
                {
                    color: '#7cb5ec',
                    width: 2,
                    value: 20,
                    dashStyle: 'shortdash',
                    label: {
                        text: 'Adult Salmonid Mortality (C)',
                        color: '#7cb5ec'
                    }
                },
                {
                    color: '#7cb5ec',
                    width: 2,
                    value: 10,
                    dashStyle: 'shortdash',
                    label: {
                        text: 'Optimum Juvenile Salmonid Growth (C)',
                        color: '#7cb5ec'
                    }
                },
                {
                    color: '#000000',
                    width: 2,
                    value: 68,
                    dashStyle: 'shortdash',
                    label: {
                        text: 'Adult Salmonid Mortality (F)'
                    }
                },
                {
                    color: '#000000',
                    width: 2,
                    value: 50,
                    dashStyle: 'shortdash',
                    label: {
                        text: 'Optimum Juvenile Salmonid Growth (F)'
                    }
                }
            ];
        }
    <?php
    }
}

$chart = new ColganCreekWatershedMonitoringChart();
