<?php
require __DIR__.'/../../../../wp-blog-header.php';

class WatershedMonitoringChart {
    protected $colgan = false;

    protected $constituent_names = [
        'pH',
        'water_temperature',
        'air_temperature',
        'turbidity',
        'dissolved_oxygen',
        'nitrates',
        'phosphates',
        'conductivity',
    ];
    protected $constituent_display_names = [
        'pH' => 'pH',
        'water_temperature' => 'Water Temperature',
        'air_temperature' => 'Air Temperature',
        'turbidity' => 'Turbidity',
        'dissolved_oxygen' => 'Dissolved Oxygen',
        'nitrates' => 'Nitrates',
        'phosphates' => 'Phosphates',
        'conductivity' => 'Conductivity',
        'salinity' => 'Salinity',
    ];
    protected $constituent_unit_names = [
        'pH' => '',
        'water_temperature' => 'Degrees',
        'air_temperature' => 'Degrees',
        'turbidity' => 'JTU',
        'dissolved_oxygen' => 'mg / L',
        'nitrates' => 'mg / L',
        'phosphates' => 'mg / L',
        'conductivity' => 'μS/cm',
        'salinity' => 'mg /L',
    ];

    public function __construct() {
        $locations = $this->get_requested_locations();
        $constituent_names = $this->get_requested_constituents();
        $data = $this->get_chart_data($locations, $constituent_names);
        if (isset($_REQUEST['data_only'])) {
            $this->output_chart_data_json($data);
        } else {
            $this->output_chart_html($locations, $constituent_names, $data);
        }
    }

    protected function colgan_creek_chart_data($posts, $consituent_names) {
        global $wpdb;
        $constituent_values = [];
        foreach ($posts as $post) :
            $post_value = [];
            $constituent_query = $wpdb->prepare(
                "
            SELECT $wpdb->postmeta.meta_value
            FROM $wpdb->postmeta
            WHERE $wpdb->postmeta.post_id = %s
            AND $wpdb->postmeta.meta_key = %s
            ",
                [$post->ID, 'sample_date']
            );
            $post_value['date'] = $wpdb->get_var($constituent_query) ?: $post->post_date;

            foreach ($consituent_names as $consituent_name) :
                $constituent_query = $wpdb->prepare(
                    "
                SELECT $wpdb->postmeta.meta_value
                FROM $wpdb->postmeta
                WHERE $wpdb->postmeta.post_id = %s
                AND $wpdb->postmeta.meta_key = %s
                ",
                    [$post->ID, $consituent_name]
                );
                $result = $wpdb->get_var($constituent_query);
                if ($result != null) {
                    $post_value[$consituent_name] = $result;
                }
            endforeach;
            $constituent_values[] = $post_value;
        endforeach;
        return $constituent_values;
    }

    protected function colgan_creek_chart_get_location_post_ids($location_name) {
        global $wpdb;
        $posts_query = $wpdb->prepare(
            "
   		SELECT *
   		FROM $wpdb->posts, $wpdb->postmeta
   		WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
   		AND $wpdb->postmeta.meta_key = 'location'
   		AND $wpdb->postmeta.meta_value = %s
   		AND $wpdb->posts.post_status = 'publish'
   		AND $wpdb->posts.post_type = '$this->sample_type'
   		ORDER BY $wpdb->postmeta.meta_value DESC",
            [$location_name]
        );
        $posts_at_location = $wpdb->get_results($posts_query, OBJECT);
        return $posts_at_location;
    }

    protected function get_requested_constituents() {
        if (isset($_REQUEST['constituent'])) {
            return [$_REQUEST['constituent']];
        }
        return $this->constituent_names;
    }

    protected function get_requested_locations() {
        return $_REQUEST['locations'];
    }

    protected function get_chart_data($locations, $constituent_names) {
        $chart_data = [];
        foreach ($locations as $location) {
            $chart_data[$location] = $this->colgan_creek_chart_data($this->colgan_creek_chart_get_location_post_ids($location), $constituent_names);
        }
        return [
            'constituents' => $constituent_names,
            'data' => $chart_data,
        ];
    }

    public function output_chart_html($locations, $constituent_names, $chart_data) {
        $this->output_chart_html_header($locations, $constituent_names, $chart_data);
        $this->output_chart_script($locations, $constituent_names, $chart_data);
        $this->output_chart_html_footer();
    }

    public function output_chart_data_json($chart_data) {
        header('Content-type: application/json');
        echo json_encode($chart_data);
    }

    protected function output_chart_html_header($locations, $constituent_names, $chart_data) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Charts!</title>
            <link rel="stylesheet" href="//cdn.datatables.net/1.10.0/css/jquery.dataTables.css" />
            <style>
                th {
                    text-align: left;
                }
                div.chart {
                    min-width: 310px;
                    height: 400px;
                    margin 0 auto;
                }
            </style>
        </head>
        <body>
        <div id="table">
            <table class="display">
                <thead>
                <tr>
                    <th>Location</th>
                    <th>Date</th>
                    <?php foreach($constituent_names as $constituent_name): ?>
                        <th><?php echo $constituent_name; ?></th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach($locations as $location) : ?>
                    <?php foreach($chart_data['data'][$location] as $data) : ?>
                        <tr>
                            <td><?php echo $location; ?></td>
                            <td><?php echo $data['date']; ?></td>
                            <?php foreach($constituent_names as $constituent_name): ?>
                                <td><?php echo $data[$constituent_name]; ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php foreach($constituent_names as $constituent_name) : ?>
    <hr />
        <div id="chart-<?php echo $constituent_name; ?>" class="chart"></div>
    <?php endforeach; ?>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script src="http://code.highcharts.com/highcharts.js"></script>
        <script src="http://code.highcharts.com/modules/exporting.js"></script>
        <script src="//cdn.datatables.net/1.10.0/js/jquery.dataTables.js"></script>
    <?php
    }

    protected function output_chart_script($locations, $constituent_names, $chart_data) {
        $output_data = [];
        foreach ($constituent_names as $constituent_name) {
            $is_temp = $this->colgan && ($constituent_name === 'water_temperature' || $constituent_name === 'air_temperature');
            $display_name = $this->constituent_display_names[$constituent_name];
            $unit_name = $this->constituent_unit_names[$constituent_name] ?: '';
            $series_data = [];
            foreach ($locations as $location) {
                $data = array_values(array_filter($chart_data['data'][$location], function($point) use ($constituent_name) {
                    return isset($point[$constituent_name]);
                }));
                $series_name = $location;
                if ($is_temp) {
                    $series_data[] = [
                        'name' => $series_name . ' (C)',
                        'data' => $data,
                    ];
                    $data_copy = $data;
                    $series_data[] = [
                        'name' => $series_name . ' (F)',
                        'data' => array_map(function($point) use ($constituent_name) {
                            $point[$constituent_name] = $point[$constituent_name] * 1.8 + 32;
                            return $point;
                        }, $data_copy),
                    ];
                } else {
                    $series_data[] = [
                        'name' => $series_name,
                        'data' => $data,
                    ];
                }
            }
            $output_data[] = [
                'name' => $constituent_name,
                'displayName' => $display_name,
                'unitName' => $unit_name,
                'data' => $series_data,
            ];
        }
        ?>
        <script>
            function createChart(name, displayName, unitName, data) {
                var chartOptions = {
                    title: {
                        text: displayName,
                        x: -20
                    },
                    xAxis: {
                        type: 'datetime',
                        dateTimeLabelFormats: {
                            hour: '%b %e %H:%M',
                            day: '%b %e',
                            week: '%b %e',
                            month: '%b %Y',
                            year: '%b %Y'
                        }
                    },
                    yAxis: {
                        title: {
                            text: unitName
                        },
                        plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }]
                    },
                    tooltip: {
                        valueSuffix: unitName
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'middle',
                        borderWidth: 0
                    },
                    series: data.map(function(series) {
                        return {
                            name: series.name,
                            data: series.data
                                    .map(function(point) {
                                        var date = new Date(point.date);
                                        //date = new Date(date.getTime() - date.getTimezoneOffset()*60000);
                                        return {
                                            x: date.getTime(),
                                            y: parseFloat(point[name])
                                        };
                                    })
                                    .sort(function(a, b) {
                                        return a.x - b.x;
                                    })
                        };
                    })
                };
                <?php $this->output_extra_chart_options(); ?>
                $('#chart-' + name).highcharts(chartOptions);
            }
            $(function() {
                $('#table table').dataTable();
                var data = <?php echo json_encode($output_data); ?>;
                data.forEach(function(constituent) {
                    createChart(constituent.name, constituent.displayName, constituent.unitName, constituent.data);
                });
            });
        </script>
    <?php
    }

    protected function output_extra_chart_options() {}

    protected function output_chart_html_footer() {
        ?>
        </body>
        </html>
    <?php
    }
}