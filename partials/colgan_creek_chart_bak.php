<?php
require __DIR__.'/../../../../wp-blog-header.php';

function colgan_creek_chart_data($posts, $consituent_names) {
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

function colgan_creek_chart_get_location_post_ids($location_name) {
    global $wpdb;
    $posts_query = $wpdb->prepare(
        "
   		SELECT *
   		FROM $wpdb->posts, $wpdb->postmeta
   		WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
   		AND $wpdb->postmeta.meta_key = 'location'
   		AND $wpdb->postmeta.meta_value = %s
   		AND $wpdb->posts.post_status = 'publish'
   		AND $wpdb->posts.post_type = 'colgan_sample'
   		ORDER BY $wpdb->postmeta.meta_value DESC",
        [$location_name]
    );
    $posts_at_location = $wpdb->get_results($posts_query, OBJECT);
    return $posts_at_location;
}

$constituent_names = [
    'pH',
    'water_temperature',
    'air_temperature',
    'turbidity',
    'dissolved_oxygen',
    'nitrates',
    'phosphates',
    'conductivity',
];
$constituent_display_names = [
    'pH' => 'pH',
    'water_temperature' => 'Water Temperature',
    'air_temperature' => 'Air Temperature',
    'turbidity' => 'Turbidity',
    'dissolved_oxygen' => 'Dissolved Oxygen',
    'nitrates' => 'Nitrates',
    'phosphates' => 'Phosphates',
    'conductivity' => 'Conductivity',
];
$constituent_unit_names = [
    'pH' => '',
    'water_temperature' => 'Degrees',
    'air_temperature' => 'Degrees',
    'turbidity' => 'JTU',
    'dissolved_oxygen' => 'mg / L',
    'nitrates' => 'mg / L',
    'phosphates' => 'mg / L',
    'conductivity' => 'μS/cm',
];
if (isset($_REQUEST['constituent'])) {
    $constituent_names = [$_REQUEST['constituent']];
}
$locations = $_REQUEST['locations'];
$chart_data = [];
foreach ($locations as $location) {
    $chart_data[$location] = colgan_creek_chart_data(colgan_creek_chart_get_location_post_ids($location), $constituent_names);
}
$chart_data = [
    'constituents' => $constituent_names,
    'data' => $chart_data,
];
if (isset($_REQUEST['data_only'])) :
    header('Content-type: application/json');
    echo json_encode($chart_data);
else :
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
        <div id="chart-<?php echo $constituent_name; ?>" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    <?php endforeach; ?>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="http://code.highcharts.com/highcharts.js"></script>
    <!--<script src="highcharts.src.js"></script>-->
    <script src="http://code.highcharts.com/modules/exporting.js"></script>
    <script src="//cdn.datatables.net/1.10.0/js/jquery.dataTables.js"></script>
    <script>
        $(function() {
            $('#table table').dataTable();
            <?php foreach($constituent_names as $constituent_name) : ?>
            <?php $is_temp = $constituent_name === 'water_temperature' || $constituent_name === 'air_temperature'; ?>
            $('#chart-<?php echo $constituent_name; ?>').highcharts({
                        title: {
                            text: '<?php echo $constituent_display_names[$constituent_name]; ?>',
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
                                text: '<?php if ($constituent_unit_names[$constituent_name]) { echo $constituent_unit_names[$constituent_name]; } ?>'
                            },
                            plotLines: [{
                                value: 0,
                                width: 1,
                                color: '#808080'
                            }]
                        },
                        tooltip: {
                            //valueSuffix: '°C'
                        },
                        legend: {
                            layout: 'vertical',
                            align: 'right',
                            verticalAlign: 'middle',
                            borderWidth: 0
                        },
                        series: [
                            <?php $n = count($locations); foreach($locations as $location) : $n--; ?>
                            {
                                name: '<?php echo $location; if ($is_temp) { echo ' (C)'; } ?>',
                                data: <?php echo json_encode($chart_data['data'][$location]); ?>.filter(function(point) {
                                        return point.hasOwnProperty('<?php echo $constituent_name; ?>');
                                    })
                                    .map(function(point) {
                                        var date = new Date(point.date);
                                        date = new Date(date.getTime() - date.getTimezoneOffset()*60000);
                                        return {
                                            x: date.getTime(),
                                            y: parseFloat(point.<?php echo $constituent_name; ?>)
                                        };
                                    }).sort(function(a, b) {
                                        return a.x - b.x;
                                    })
                    }<?php if ($n > 0) {?>,<?php } ?>
                    <?php endforeach; ?>
                    <?php if ($is_temp) : ?>
                    ,<?php $n = count($locations); foreach($locations as $location) : $n--; ?>
                    {
                        name: '<?php echo $location; ?> (F)',
                        data: <?php echo json_encode($chart_data['data'][$location]); ?>.filter(function(point) {
                                return point.hasOwnProperty('<?php echo $constituent_name; ?>');
                            }).map(function(point) {
                                var date = new Date(point.date);
                                date = new Date(date.getTime() - date.getTimezoneOffset()*60000);
                                return {
                                    x: date.getTime(),
                                    y: parseFloat(point.<?php echo $constituent_name; ?>) * 1.8 + 32
                                };
                            }).sort(function(a, b) {
                                return a.x - b.x;
                            })
                    }<?php if ($n > 0) {?>,<?php } ?>
        <?php endforeach; ?>
        <?php endif; ?>
        ]
        });
        <?php endforeach; ?>
        });
    </script>
    </body>
    </html>
<?php endif;