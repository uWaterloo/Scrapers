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
  
  // Column data keys
  $col_keys = array('course_id', 'section', 'campus', 'associated_class', 
                    'related_component_1', 'related_component_2', 'enrollment_capacity',
                    'enrollment_total', 'waiting_capacity', 'waiting_total');
  
  $numeric_col_keys = array('enrollment_capacity', 'enrollment_total', 'waiting_capacity', 'waiting_total');
  
  // Additional data that may exist for a section
  $additional_keys = array('topic');
  $additional_keys_arrays = array('reserves', 'classes', 'held_with');
  
  // Column keys for class data
  $class_col_keys = array('date', 'location', 'instructors');
  
  // Additional data for classes ('dates')
  $class_additional_keys = array();
  $class_additional_keys_arrays = array('instructors');
  
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
        
        // Variables for use while iterating and for storing found data
        $index       = 0;
        $reserve     = array();
        $class_class = array();
        
        $new_class = $active_course;
        
        /** CLASS INFORMATION **/
        foreach ($col_keys as $key) {
          $new_class[$key] = '';
        }// end of foreach
        
        foreach ($additional_keys as $key) {
          $new_class[$key] = '';
        }// end of foreach
        
        foreach ($additional_keys_arrays as $key) {
          $new_class[$key] = array();
        }// end of foreach
                  
        $new_class['classes'] = array();
        
        // Configure the class_class
        foreach ($class_col_keys as $key) {
          $class_class[$key] = '';
        }// end of foreach
        
        foreach ($class_additional_keys as $key) {
          $class_class[$key] = '';
        }// end of foreach
        
        foreach ($class_additional_keys_arrays as $key) {
          $class_class[$key] = array();
        }// end of foreach
        
        // Variable to store if a class was found on this row
        $dateFound = false;
        
        // Iterate over each cell
        foreach ($row->find('td') as $td) { 
          // Take action for special cells
          if ($index == 0 && $td->colspan == 10) {  // Either Held With or Topic
            $rawData = $td->innertext;
            $data = beautify($rawData, true);
            
            // Take action based on the data
            if (preg_match('/Held With/', $rawData) == 1) {
              $classes[count($classes) - 1]['held_with'][] = $data;
            } else if (preg_match('/Topic/', $rawData) == 1) {
              $classes[count($classes) - 1]['topic'] = $data;
            }// end of if/else
          } else if ($index < count($col_keys) && count($row->find('td[colspan=6]')) > 0) { // Reserve
            if ($index == 0) {
              $reserve['reserve_group'] = beautify($td->innertext, true);
            } else {
              $text = beautify($td->innertext);
              if ($text) {
                $reserve[$col_keys[$index]] = $text;
              }// end of if
            }// end of if/else
          } else if ($index < count($col_keys)) {
            $new_class[$col_keys[$index]] = beautify($td->innertext);
          } else {
            if ($class_col_keys[$index - count($col_keys)] == 'date') {
              $value = beautify($td->innertext);
              $class_class[$class_col_keys[$index - count($col_keys)]] = parse_date($value);
                              
              if ($value != '') {
                $dateFound = true;
              }// end of if
            } else if ($class_col_keys[$index - count($col_keys)] == 'location') {
              $class_class[$class_col_keys[$index - count($col_keys)]] = parse_location($td->innertext);
            } else if ($class_col_keys[$index - count($col_keys)] == 'instructors') {
              if ($dateFound) {
                $class_class[$class_col_keys[$index - count($col_keys)]][] = beautify($td->innertext);
              } else {
                $previousClass = $classes[count($classes) - 1]['classes'][count($classes[count($classes) - 1]['classes']) - 1];
                $previousClass['instructors'][] = beautify($td->innertext);
                
                $classes[count($classes) - 1]['classes'][count($classes[count($classes) - 1]['classes']) - 1] = $previousClass;
              }// end of if/else
            } else {
              $class_class[$class_col_keys[$index - count($col_keys)]] = beautify($td->innertext);
            }// end of if/else
          }// end of if/else if/else
          
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
        
        // Add a reserve, it it was found
        if (isset($reserve['reserve_group'])) {
          $classes[count($classes) - 1]['reserves'][] = $reserve;
        }
        
        if ($class_class['date']['is_cancelled'] || $class_class['date']['is_closed']) {
          $previousClass = $classes[count($classes) - 1]['classes'][count($classes[count($classes) - 1]['classes']) - 1];
          
          $previousClass['date']['is_cancelled'] = $class_class['date']['is_cancelled'];
          $previousClass['date']['is_closed']    = $class_class['date']['is_closed'];
          
          $classes[count($classes) - 1]['classes'][count($classes[count($classes) - 1]['classes']) - 1] = $previousClass;
        } else {
          $new_class['classes'][] = $class_class;
        }// end of if/else
        
        // handle multiple classes for one section
        if ($dateFound && !is_numeric($new_class[$col_keys[0]])) {
          $classes[count($classes) - 1]['classes'] = array_merge($classes[count($classes) - 1]['classes'], $new_class['classes']);
        } else if (is_numeric($new_class[$col_keys[0]])) { // new class was set
          $classes[] = $new_class;
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
    $data = str_replace('Held With: ', '', $data);
    $data = str_replace('Topic: ', '', $data);
  }// end of if
  
  return $data;
}// end of beatify_data method

function parse_date($strDate) {
  // Ensure data is cleared of unwanted characters.
  $strDate = beautify($strDate);
  
  $date = array('start_time' => '',
                'end_time'   => '',
                'weekdays'  => '',
                'start_date' => '',
                'end_date'   => '');
  
  $match      = array();
  
  $date['is_tba'] = preg_match("/TBA/", $strDate) == 1;
  $date['is_cancelled'] = preg_match("/Cancelled Section/", $strDate) == 1;
  $date['is_closed'] = preg_match("/Closed Section/", $strDate) == 1;
  
  $strDate = beautify($strDate);
  $matchResult = preg_match("/(\d{2}:\d{2})-(\d{2}:\d{2})(\w+)?\s*(?:(\d{2}\/\d{2})-(\d{2}\/\d{2}))?.*/", $strDate, $match);
      
  // match was successful
  if ($matchResult === 1) {
    if (count($match) >= 3) {
      $parsed_date = normalize_date($match[1], $match[2]);
      $date['start_time'] = $parsed_date[0];
      $date['end_time'] = $parsed_date[1];
      
      if (count($match) >= 4) {
        $date['weekdays']   = $match[3];
      
        if (count($match) >= 6) {
          $date['start_date'] = $match[4];
          $date['end_date']   = $match[5];
        }// end of if
      }// end of if
    }
  }// end of if
  
  return $date;
}// End of parse_date function

function parse_location($strLocation) {
  // Ensure data is 'beautiful'
  $strLocation = beautify($strLocation);
  
  // Arrays for use
  $location = array('building' => '', 'room' => '');
  
  // Regular expresion
  $locationMatch = array();
  $locationRegex = preg_match('/(\S+) (\S+)/', $strLocation, $locationMatch);
  
  if ($locationRegex == 1 && count($locationMatch >= 3)) {
    $location['building'] = $locationMatch[1];
    $location['room']     = $locationMatch[2];
  }// end of if
  
  return $location;
}// End of parse_location function


function normalize_date($start, $end) {
  list($start_hr, $start_min) = explode(':', $start);
  list($end_hr, $end_min)     = explode(':', $end); 

  // takes advantage of loose typing
  if($start_hr > $end_hr) {
    $end_hr += 12;
  }

  if($start_hr <= 7) {
    $start_hr += 12;
    $end_hr += 12;
  }

  if($end_hr == 12 && $end_min == 0) {
    $end_hr = '00';
  }

  return array($start_hr.':'.$start_min, $end_hr.':'.$end_min);
}


// Usage
print_r(parse_schedule('1139', 'PHYS', '236'));
print_r(parse_schedule('1139', 'PHYS', '131L'));
print_r(parse_schedule('1139', 'PHYS', '454'));
print_r(parse_schedule('1139', 'PHYS', '353L'));
print_r(parse_schedule('1139', 'PHYS', '121L'));
print_r(parse_schedule('1139', 'CS', '246'));

print_r(parse_schedule('1139', 'PHYS', '131L'));
print_r(parse_schedule('1131', 'PHYS', '380'));
print_r(parse_schedule('1139', 'PHYS', '490'));
print_r(parse_schedule('1139', 'PHYS', '771', 'grad'));

print_r(parse_schedule('1139', 'PHYS', '611', 'grad'));

?>
