<?php

require 'dom.class.php';


/*
  This is the barebone code for parsing the schedule page.
  This code has been derived from Jeff Verkoeyen's uwdata.ca scraper
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
                    'enrollment_total', 'waiting_capacity', 'waiting_total');
  $numeric_col_keys = array('enrollment_capacity', 'enrollment_total', 'waiting_capacity', 'waiting_total');
                    
  $class_col_keys = array('dates', 'location', 'instructor');
  
  $html = str_get_html($data);
  unset($data);
  
  $classes        = array();
  $active_course  = null;
  $active_section = null;
  
  foreach($html->find('table[border=2] > tr') as $tr) {
    /** COURSE INFORMATION **/
    if(count($tr->find('td')) == 4) {
      $course_info   = $tr->find('td');
      $active_course = array('department' => beautify($course_info[0]->innertext),
                             'number'     => beautify($course_info[1]->innertext),
                             'credits'    => beautify($course_info[2]->innertext),
                             'title'      => beautify($course_info[3]->innertext));
    }
    
    if(count($tr->find('td[colspan=4]')) == 1) {
      $active_course['note'] = beautify($tr->find('td[colspan=4]', 0)->innertext, true);
    }
    
    /** CLASSES/SECTIONS **/
    if(count($tr->find('td[colspan=3]')) == 1) {
      $course_table = $tr->find('td[colspan=3] table', 0);
      
      foreach ($course_table->find('tr') as $row) {
        // Ensure we are not on a header row
        if(count($row->find('th')) > 0) {
          continue;
        }// end of if
        
        // Ensure we are not a full-width row
        if(count($row->find('td[colspan=10]')) > 0) {
          continue;
        }// end of if
        
        /** RESERVES **/
        if(count($row->find('td[colspan=6]')) > 0) {
          $index         = 0;
          $reserve       = array();
          $class_class   = array();
          
          foreach ($class_col_keys as $key) {
            $class_class[$key] = '';
          }// end of foreach
          
          foreach($row->find('td') as $td) {
            if ($index == 0) {
              $reserve['reserve_group'] = beautify($td->innertext, true);
            } else if ($index < count($col_keys)) {
              $text = beautify($td->innertext);
              if ($text) {
                $reserve[$col_keys[$index]] = $text;
              }// end of if
            } else {
              if ($class_col_keys[$index - count($col_keys)] == 'dates') {
                $class_class[$class_col_keys[$index - count($col_keys)]] = parse_date($td->innertext);
              } else {
                $class_class[$class_col_keys[$index - count($col_keys)]] = beautify($td->innertext);
              }// end of if/else
            }// end of if/else
            
            $index += max(1, intval($td->colspan));
          }// end of foreach
          
          if ($class_class['dates'] != '&nbsp') {
            $classes[count($classes) - 1]['classes'][] = $class_class;
          }// end of if
          
          if(!isset($classes[count($classes) - 1]['reserves'])) {
            $classes[count($classes) - 1]['reserves'] = array();
          }// end of if
          
          $classes[count($classes) - 1]['reserves'][] = $reserve;
        } else {
          $index     = 0;
          $new_class = $active_course;
          
          /** CLASS INFORMATION **/
          foreach ($col_keys as $key) {
            $new_class[$key] = '';
          }// end of foreach
          
          $new_class['reserves'] = array();
                    
          $new_class['classes'] = array();
          $new_class_class      = array();
          
          foreach ($class_col_keys as $key) {
            $new_class_class[$key] = '';
          }// end of foreach
          
          foreach ($row->find('td') as $td) {             
            if ($index < count($col_keys)) {
              $new_class[$col_keys[$index]] = beautify($td->innertext);
            } else {
              if ($class_col_keys[$index - count($col_keys)] == 'dates') {
                $new_class_class[$class_col_keys[$index - count($col_keys)]] = parse_date($td->innertext);
              } else {
                $new_class_class[$class_col_keys[$index - count($col_keys)]] = beautify($td->innertext);
              }// end of if/else
            }// end of if/else
            
            $index += max(1, intval($td->colspan));
          }// end of foreach
          
          // ensure any numeric fields have a numeric value
          foreach ($new_class as $key => $value) {
            if (in_array($key, $numeric_col_keys)) {
              if (!is_numeric($value)) {
                $new_class[$key] = 0; // give it a value of 0
              }// end of if
            }// end of if
          }// end of foreach
          
          $new_class['classes'][] = $new_class_class;
          
          // handle multiple classes for one section
          if (!is_numeric($new_class[$col_keys[0]])) {
            $classes[count($classes) - 1]['classes'] = array_merge($classes[count($classes) - 1]['classes'], $new_class['classes']);
          } else {
            $classes[] = $new_class;
          }// end of if/else
        }// end of if/else
      }// end of foreach
    }// end of if
  }// end of if
  
  $html->__destruct();
  unset($html);
  
  return $classes;
}// end of parse_schedule method

/**
 * Beautifies the text output.
 *
 * PARAMS:
 *    $data : The data to beatify
 *    $removeHeaders : Should headers be removed from the data (Ex. Notes: or Reserve:). Defaults to false.
 *
 * RETURNS:
 *    Beautified data.
 */
function beautify($data, $removeHeaders = false) {
  $data = trim($data);
  
  // remove unwanted components
  $data = str_replace('&nbsp', '', $data);
  $data = preg_replace('/\s{2,}/i', ' ', $data);
  
  // remove html tags
  $data = preg_replace('/\<\/?i\>/i', '', $data);
  $data = preg_replace('/\<\/?b\>/i', '', $data);
  $data = preg_replace('/\<\/?br\>/i', ' ', $data);
  
  // remove headers
  if ($removeHeaders) {
    $data = str_replace('Notes: ', '', $data);
    $data = str_replace('Reserve: ', '', $data);
  }// end of if
  
  return $data;
}// end of beatify_data method

function parse_date($strDate) {
  $date = array('start_time' => '',
                'end_time'   => '',
                'weekdays'  => '',
                'start_date' => '',
                'end_date'   => '');
  $match = array();
  
  $strDate = beautify($strDate);
  $dateRegex = "/(\d{2}:\d{2})-(\d{2}:\d{2})(\w+)\s*(?:(\d{2}\/\d{2})-(\d{2}\/\d{2}))?.*/";
  $matchResult = preg_match($dateRegex, $strDate, $match);
      
  // match was successful
  if ($matchResult === 1) {
    if (count($match) >= 4) {
      $date['start_time'] = $match[1];
      $date['end_time']   = $match[2];
      $date['weekdays']   = $match[3];
      
      if (count($match) >= 6) {
        $date['start_date'] = $match[4];
        $date['end_date']   = $match[5];
      }// end of if
    }
  }// end of if
  
  return $date;
}// End of parse_date function

// Usage
print_r(parse_schedule('1139', 'PHYS', '236'));
print_r(parse_schedule('1139', 'PHYS', '131L'));
print_r(parse_schedule('1139', 'PHYS', '454'));
print_r(parse_schedule('1139', 'PHYS', '353L'));
print_r(parse_schedule('1139', 'PHYS', '121L'));
print_r(parse_schedule('1139', 'CS', '246'));


?>
