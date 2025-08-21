<?php
/**
 * LinkedIn Data Import Helper for Career Progression
 * Handles conversion of LinkedIn exported data to plugin format
 */

if (!defined('ABSPATH')) {
    exit;
}

class CPV_LinkedIn {
    
    public function init() {
        // No OAuth needed - just data conversion
    }
    
    /**
     * Parse LinkedIn export data and convert to our schema
     * Accepts the JSON array from LinkedIn's Positions.json export file
     */
    public static function parse_linkedin_export($linkedin_positions) {
        if (!is_array($linkedin_positions)) {
            return false;
        }
        
        // Group positions by career path type
        $paths = array(
            'Engineering Path' => array(),
            'Management Path' => array(),
            'Business Path' => array(),
            'Design Path' => array(),
            'Other Path' => array()
        );
        
        foreach ($linkedin_positions as $position) {
            $title_lower = strtolower($position['title'] ?? '');
            
            // Categorize based on title keywords
            if (strpos($title_lower, 'manager') !== false || 
                strpos($title_lower, 'director') !== false || 
                strpos($title_lower, 'vp') !== false ||
                strpos($title_lower, 'vice president') !== false ||
                strpos($title_lower, 'president') !== false ||
                strpos($title_lower, 'chief') !== false ||
                strpos($title_lower, 'head of') !== false ||
                strpos($title_lower, 'lead') !== false && strpos($title_lower, 'team') !== false) {
                $path = 'Management Path';
            } elseif (strpos($title_lower, 'engineer') !== false || 
                     strpos($title_lower, 'developer') !== false ||
                     strpos($title_lower, 'programmer') !== false ||
                     strpos($title_lower, 'architect') !== false ||
                     strpos($title_lower, 'technical') !== false ||
                     strpos($title_lower, 'software') !== false) {
                $path = 'Engineering Path';
            } elseif (strpos($title_lower, 'design') !== false || 
                     strpos($title_lower, 'ux') !== false ||
                     strpos($title_lower, 'ui') !== false ||
                     strpos($title_lower, 'creative') !== false ||
                     strpos($title_lower, 'graphic') !== false) {
                $path = 'Design Path';
            } elseif (strpos($title_lower, 'sales') !== false || 
                     strpos($title_lower, 'marketing') !== false ||
                     strpos($title_lower, 'business') !== false ||
                     strpos($title_lower, 'product') !== false ||
                     strpos($title_lower, 'consultant') !== false ||
                     strpos($title_lower, 'analyst') !== false) {
                $path = 'Business Path';
            } else {
                $path = 'Other Path';
            }
            
            // Convert LinkedIn date format
            $start_year = $position['startDate']['year'] ?? date('Y');
            $start_month = $position['startDate']['month'] ?? 1;
            $end_year = $position['endDate']['year'] ?? null;
            $end_month = $position['endDate']['month'] ?? null;
            
            $job = array(
                'name' => $position['companyName'] ?? 'Unknown Company',
                'title' => $position['title'] ?? 'Position',
                'dates' => date('F Y', mktime(0, 0, 0, $start_month, 1, $start_year)) . ' - ' . 
                          ($end_year ? date('F Y', mktime(0, 0, 0, $end_month, 1, $end_year)) : 'Present'),
                'startYear' => intval($start_year),
                'endYear' => $end_year ? intval($end_year) : intval(date('Y')) + 1,
                'type' => 'job',
                'description' => $position['description'] ?? '',
                'location' => $position['location'] ?? ''
            );
            
            // Add skills if available
            if (!empty($position['skills'])) {
                $job['skills'] = $position['skills'];
            }
            
            $paths[$path][] = $job;
        }
        
        // Remove empty paths
        $paths = array_filter($paths, function($jobs) {
            return !empty($jobs);
        });
        
        // Build the hierarchical structure
        $career_data = array(
            'name' => 'Career Journey',
            'startYear' => 2000,
            'children' => array()
        );
        
        $path_colors = array(
            'Engineering Path' => '#48bb78',
            'Management Path' => '#9f7aea',
            'Business Path' => '#4299e1',
            'Design Path' => '#ed8936',
            'Other Path' => '#718096'
        );
        
        foreach ($paths as $path_name => $jobs) {
            if (empty($jobs)) continue;
            
            // Sort jobs by start year (oldest first)
            usort($jobs, function($a, $b) {
                return $a['startYear'] - $b['startYear'];
            });
            
            $career_data['children'][] = array(
                'name' => $path_name,
                'type' => 'path',
                'color' => $path_colors[$path_name] ?? '#4299e1',
                'startYear' => min(array_column($jobs, 'startYear')),
                'description' => '',
                'children' => $jobs
            );
        }
        
        // Update the overall start year
        if (!empty($career_data['children'])) {
            $career_data['startYear'] = min(array_column($career_data['children'], 'startYear'));
        }
        
        return $career_data;
    }
    
    /**
     * Convert LinkedIn export to database entries
     * Returns array ready for database insertion
     */
    public static function convert_to_db_format($linkedin_positions) {
        $db_entries = array();
        
        foreach ($linkedin_positions as $position) {
            $start_year = $position['startDate']['year'] ?? date('Y');
            $start_month = $position['startDate']['month'] ?? 1;
            $end_year = $position['endDate']['year'] ?? null;
            $end_month = $position['endDate']['month'] ?? null;
            
            // Determine path type
            $title_lower = strtolower($position['title'] ?? '');
            $path_type = 'General Path';
            $path_color = '#4299e1';
            
            if (strpos($title_lower, 'engineer') !== false || strpos($title_lower, 'developer') !== false) {
                $path_type = 'Engineering Path';
                $path_color = '#48bb78';
            } elseif (strpos($title_lower, 'manager') !== false || strpos($title_lower, 'director') !== false) {
                $path_type = 'Management Path';
                $path_color = '#9f7aea';
            } elseif (strpos($title_lower, 'design') !== false) {
                $path_type = 'Design Path';
                $path_color = '#ed8936';
            }
            
            $db_entries[] = array(
                'position' => $position['title'] ?? 'Position',
                'company' => $position['companyName'] ?? 'Unknown Company',
                'start_date' => sprintf('%04d-%02d-01', $start_year, $start_month),
                'end_date' => $end_year ? sprintf('%04d-%02d-01', $end_year, $end_month) : null,
                'description' => $position['description'] ?? '',
                'skills' => json_encode($position['skills'] ?? array()),
                'achievements' => json_encode(array()),
                'salary' => null,
                'location' => $position['location'] ?? '',
                'path_type' => $path_type,
                'path_color' => $path_color
            );
        }
        
        return $db_entries;
    }
}