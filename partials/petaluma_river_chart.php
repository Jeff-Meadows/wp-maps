<?php
require_once 'WatershedMonitoringChart.php';

class PetalumaWatershedMonitoringChart extends WatershedMonitoringChart {
    public function __construct() {
        $this->colgan = false;
        $this->constituent_names[] = 'salinity';
        $this->constituent_unit_names['conductivity'] = 'μmho/cm';
        $this->constituent_unit_names['turbidity'] = 'NTU';
        $this->constituent_unit_names['water_temperature'] = 'Degrees C';
        $this->constituent_unit_names['air_temperature'] = 'Degrees C';
        $this->sample_type = 'fopr_sample';
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
                        text: 'Adult Salmonid Mortality',
                        color: '#7cb5ec'
                    }
                },
                {
                    color: '#7cb5ec',
                    width: 2,
                    value: 10,
                    dashStyle: 'shortdash',
                    label: {
                        text: 'Optimum Juvenile Salmonid Growth',
                        color: '#7cb5ec'
                    }
                }
            ];
        }
<?php
    }
}

$chart = new PetalumaWatershedMonitoringChart();
