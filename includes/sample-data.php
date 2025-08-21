<?php
/**
 * Sample career data for initial setup
 */

function cpv_get_sample_career_data() {
    return array(
        'name' => 'Career Journey',
        'type' => 'root',
        'children' => array(
            array(
                'name' => 'IT Path',
                'type' => 'path',
                'color' => '#4299e1',
                'children' => array(
                    array(
                        'name' => 'OfficeMax Print & Document Services',
                        'title' => 'IT Support Specialist',
                        'type' => 'job',
                        'startYear' => 2003,
                        'endYear' => 2005,
                        'dates' => '2003 - 2005',
                        'description' => 'Provided technical support and document management solutions.',
                        'skills' => array('Technical Support', 'Document Management', 'Customer Service'),
                        'location' => 'Location TBD'
                    ),
                    array(
                        'name' => 'FedEx Kinko\'s',
                        'title' => 'Systems Administrator',
                        'type' => 'job',
                        'startYear' => 2005,
                        'endYear' => 2007,
                        'dates' => '2005 - 2007',
                        'description' => 'Managed IT infrastructure and systems.',
                        'skills' => array('System Administration', 'Network Management', 'IT Infrastructure'),
                        'location' => 'Location TBD'
                    ),
                    array(
                        'name' => 'Robert Half & TEKsystems',
                        'title' => 'IT Consultant',
                        'type' => 'job',
                        'startYear' => 2007,
                        'endYear' => 2009,
                        'dates' => '2007 - 2009',
                        'description' => 'Provided IT consulting services to various clients.',
                        'skills' => array('IT Consulting', 'Project Management', 'Client Relations'),
                        'location' => 'Location TBD'
                    )
                )
            ),
            array(
                'name' => 'Design Path',
                'type' => 'path',
                'color' => '#ed8936',
                'children' => array(
                    array(
                        'name' => 'Robert Half & TEKsystems',
                        'title' => 'UX Designer',
                        'type' => 'job',
                        'startYear' => 2009,
                        'endYear' => 2011,
                        'dates' => '2009 - 2011',
                        'description' => 'Transitioned to UX design, creating user interfaces and experiences.',
                        'skills' => array('UX Design', 'UI Design', 'Prototyping'),
                        'location' => 'Location TBD'
                    ),
                    array(
                        'name' => 'Apogee Physicians',
                        'title' => 'Senior UX Designer',
                        'type' => 'job',
                        'startYear' => 2011,
                        'endYear' => 2013,
                        'dates' => '2011 - 2013',
                        'description' => 'Led design initiatives for healthcare applications.',
                        'skills' => array('Healthcare UX', 'Design Leadership', 'User Research'),
                        'location' => 'Location TBD'
                    ),
                    array(
                        'name' => 'Encore Discovery Solutions',
                        'title' => 'Design Director',
                        'type' => 'job',
                        'startYear' => 2013,
                        'endYear' => 2015,
                        'dates' => '2013 - 2015',
                        'description' => 'Directed design team and strategy.',
                        'skills' => array('Design Direction', 'Team Leadership', 'Strategic Design'),
                        'location' => 'Location TBD'
                    )
                )
            ),
            array(
                'name' => 'Engineering Path',
                'type' => 'path',
                'color' => '#48bb78',
                'children' => array(
                    array(
                        'name' => 'Pan Am Education',
                        'title' => 'Full Stack Developer',
                        'type' => 'job',
                        'startYear' => 2015,
                        'endYear' => 2017,
                        'dates' => '2015 - 2017',
                        'description' => 'Developed educational platforms and applications.',
                        'skills' => array('Full Stack Development', 'JavaScript', 'React', 'Node.js'),
                        'location' => 'Location TBD'
                    ),
                    array(
                        'name' => 'Salucro Healthcare Solutions',
                        'title' => 'Senior Software Engineer',
                        'type' => 'job',
                        'startYear' => 2017,
                        'endYear' => 2019,
                        'dates' => '2017 - 2019',
                        'description' => 'Built healthcare payment processing systems.',
                        'skills' => array('Healthcare Tech', 'Payment Systems', 'API Development'),
                        'location' => 'Location TBD'
                    ),
                    array(
                        'name' => 'Art In Reality, LLC',
                        'title' => 'Technical Lead',
                        'type' => 'job',
                        'startYear' => 2019,
                        'endYear' => 2021,
                        'dates' => '2019 - 2021',
                        'description' => 'Led technical initiatives for AR/VR projects.',
                        'skills' => array('AR/VR', 'Technical Leadership', '3D Graphics'),
                        'location' => 'Location TBD'
                    ),
                    array(
                        'name' => 'GoDaddy',
                        'title' => 'Principal Engineer',
                        'type' => 'job',
                        'startYear' => 2021,
                        'endYear' => null,
                        'dates' => '2021 - Present',
                        'description' => 'Leading engineering initiatives at scale.',
                        'skills' => array('System Architecture', 'Cloud Infrastructure', 'Engineering Leadership'),
                        'location' => 'Remote'
                    )
                )
            )
        )
    );
}

function cpv_insert_sample_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'career_progression';
    
    // Check if data already exists
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    if ($count > 0) {
        return; // Data already exists
    }
    
    // Flatten the hierarchical data for database storage
    $sample_data = cpv_get_sample_career_data();
    
    foreach ($sample_data['children'] as $path) {
        foreach ($path['children'] as $job) {
            $wpdb->insert(
                $table_name,
                array(
                    'position' => $job['title'],
                    'company' => $job['name'],
                    'start_date' => $job['startYear'] . '-01-01',
                    'end_date' => $job['endYear'] ? $job['endYear'] . '-12-31' : null,
                    'description' => $job['description'],
                    'skills' => json_encode($job['skills']),
                    'achievements' => json_encode(array()),
                    'salary' => null,
                    'location' => $job['location'],
                    'path_type' => $path['name'],
                    'path_color' => $path['color']
                )
            );
        }
    }
}