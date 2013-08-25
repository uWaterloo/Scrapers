<?php

require 'dom.class.php';


/*
  This is the barebone code for parsing the schedule page.
  See usage below.
*/

function parse_schedule($term_id, $faculty, $course, $level = 'under')
{
  
  $query = http_build_query(array('sess'    => $term_id,
                                  'subject' => $faculty,
                                  'level'   => $level,
                                  'cournum' => $course));
  
  $data = file_get_contents('http://www.adm.uwaterloo.ca/cgi-bin/cgiwrap/infocour/salook.pl?'.$query);
  
  $col_keys = array('course_id', 'section', 'campus', 'associated_classes', 
                    'related_component_1', 'related_component_2', 'enrollment_capacity',
                    'enrollment_total', 'waiting_capacity', 'waiting_total', 'dates',
                    'location', 'instructor');
  
  $html = str_get_html($data);
  unset($data);
  
  $classes       = array();
  $active_course = null;
  
  foreach($html->find('table[border=2] > tr') as $tr) {
    if(count($tr->find('td')) == 4) {
      $course_info   = $tr->find('td');
      $active_course = array('department' => trim($course_info[0]->innertext),
                             'number'     => trim($course_info[1]->innertext),
                             'credits'    => trim($course_info[2]->innertext),
                             'title'      => trim($course_info[3]->innertext));
    }
    
    if(count($tr->find('td[colspan=4]')) == 1) {
      $active_course['note'] = trim($tr->find('td[colspan=4]', 0)->innertext);
    }
    
    if(count($tr->find('td[colspan=3]')) == 1) {
      $course_table = $tr->find('td[colspan=3] table', 0);
      
      foreach($course_table->find('tr') as $row) {
        if(count($row->find('th')) > 0) {
          continue;
        }
        
        if(count($row->find('td[colspan=10]')) > 0) {
          continue;
        }
        
        if(count($row->find('td[colspan=6]')) > 0) {
          $index   = 0;
          $reserve = array();

          foreach($row->find('td') as $td) {
            if (0 == $index) {
              $reserve['reserve_group'] = trim($td->innertext);
            } else {
              $text = trim($td->innertext);
              if($text) {
                $reserve[$col_keys[$index]] = $text;
              }
            }
            
            $index += max(1, intval($td->colspan));
          }
          
          if(!isset($classes[count($classes) - 1]['reserves'])) {
            $classes[count($classes) - 1]['reserves'] = array();
          }
          
          $classes[count($classes) - 1]['reserves'][] = $reserve;
        } else {
          $index     = 0;
          $new_class = $active_course;
          
          foreach($col_keys as $key) {
            $new_class[$key] = '';
          }
          
          foreach($row->find('td') as $td) {
            $new_class[$col_keys[$index]] = trim($td->innertext);
            $index += max(1, intval($td->colspan));
          }
          
          $classes[] = $new_class;
        }
      }
    }
  }
  
  $html->__destruct();
  unset($html);
  
  return $classes;
}


// Usage
print_r(parse_schedule('1139', 'PHYS', '236'));
print_r(parse_schedule('1139', 'PHYS', '131L'));
print_r(parse_schedule('1139', 'PHYS', '454'));
print_r(parse_schedule('1139', 'PHYS', '353L'));
print_r(parse_schedule('1139', 'PHYS', '121L'));
print_r(parse_schedule('1139', 'CS', '246'));


?>
