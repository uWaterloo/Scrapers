<?php

require 'dom.class.php';


function parse_courses($subject)
{
  // testing only undergrad courses for now
  $url = "http://www.ucalendar.uwaterloo.ca/1314/COURSE/course-".$subject.".html";
  $html = str_get_html(file_get_contents($url));
  $elm  = $html->find('table');

  $template = array(
              'id' => null,
              'department' => null,
              'number' => null,
              'title' => null,
              'credits' => null,
              'description' => null,
              'instructions' => array(),
              'prerequisites' => null,
              'antirequisites' => null,
              'corequisites' => null,
              'crosslistings' => null,
              'terms_offered' => array(),
              'offerings' => array(
                'online' => false,
                'online_only' => false,
                'st_jerome' => false,
                'st_jerome_only' => false,
                'renison' => false,
                'renison_only' => false,
                'conrad_grebel' => false,
                'conrad_grebel_only' => false
              ),
              'needs_department_consent' => false,
              'needs_instructor_consent' => false,
              'extra' => null,
              'notes' => null,
              'calendar_year' => null,
              'type' => null,
              'url' => null
              );


  $is_grad = strpos($url, 'GRDcourse') !== false;
  $itype   = ($is_grad) ? 'graduate' : 'undergraduate';
  $urldata = explode('/', $url);
  $year    = ($is_grad) ? $urldata[5] : $urldata[3];

  foreach($elm as $table)
  {
    if($table->width == '80%')
    {
      $course  = $template;
      $tr      = $table->find('tr');

      $leftCol = current(current($tr)->find('td[align=left]'));
      $anchor  = current($leftCol->find('a'))->name;
      $data    = strip_tags($leftCol->innertext);

      if(!$is_grad && preg_match('/([a-z]+) ([a-z0-9]+) ([a-z,]+) ([0-9\.]+)/i', $data, $matches))
      {
        $course['department'] = $matches[1];
        $course['number']     = $matches[2];
        $course['_offerings'] = $matches[3];
        $course['credits']    = $matches[4];
        $course['url']        = $url.'#'.$anchor;
      } 
      elseif($is_grad && preg_match('/([A-Z]+) ([A-z0-9]+) (.*) \(([0-9\.]+)\) ([A-Z,]+)/i', $data, $matches))
      {
        $course['department'] = $matches[1];
        $course['number']     = $matches[2];
        $course['title']      = sanitize($matches[3]);
        $course['credits']    = $matches[4];
        $course['_offerings'] = $matches[5];
        $course['url']        = $url.'#'.$anchor;
      }
      else
      {
        return array();
      }

      $course['type'] = $itype;
      $course['calendar_year'] = $year;

      $offerings = explode(',', $course['_offerings']);
      $instructions = array('cln', 'dis', 'ens', 'ess', 'fld', 'lab', 'lec', 'orl', 'wrk',
                            'pra', 'prj', 'rdg', 'sem', 'stu', 'tlc', 'tst', 'tut', 'wsp');

      foreach($offerings as $offering)
      {
        if(in_array(strtolower($offering), $instructions))
        {
          $course['instructions'][] = $offering;
        }
      }

      unset($course['_offerings']);

      $data = current(current($tr)->find('td[align=right]'))->innertext;
      preg_match('/Course ID: ([0-9]+)/i', $data, $matches);
      $course['id'] = $matches[1];
      next($tr);

      if(!$is_grad)
      {
        $data = current(current($tr)->find('td'))->innertext;
        $course['title'] = sanitize(strip_tags($data));
        next($tr);
      }
      else
      {
        $data = strip_tags(current(current($tr)->find('td'))->innertext);
        if(strpos($data, '(Cross-listed') === 0)
        {
          $course['crosslistings'] = trim(str_replace('(Cross-listed with ', '', $data), '() .');
          next($tr);
        }
      }


      $data = current(current($tr)->find('td'))->innertext;
      $course['description'] = sanitize(strip_tags($data));
      next($tr);

      $extra_fields = array();

      while(current($tr))
      {
        $data = ltrim(trim(strip_tags(current(current($tr)->find('td'))->innertext)), '.');

        if($data)
        {
          if(strpos($data, 'Prereq') === 0)
          {
            $course['prerequisites'] = str_replace('Prereq: ', '', $data);
          }
          elseif(strpos($data, 'Antireq') === 0)
          {
            $course['antirequisites'] = str_replace('Antireq: ', '', $data);
          }
          elseif(strpos(ltrim($data, '('), 'Coreq') === 0)
          {
            $course['corequisites'] = str_replace('Coreq: ', '', $data);
          }
          elseif(strpos($data, '(Cross-listed') === 0)
          {
            $course['crosslistings'] = trim(str_replace('(Cross-listed with ', '', $data), '() .');
          }
          elseif(strpos($data, 'Also offered by Distance Education') === 0 || strpos($data, 'Also offered Online') === 0)
          {
            $course['offerings']['online'] = true;
          }
          elseif(strpos($data, 'Only offered by Distance Education') === 0|| strpos($data, 'Only offered Online') === 0)
          {
            $course['offerings']['online_only'] = true;
          }
          elseif(strpos($data, 'Offered at St. Jerome\'s University') === 0)
          {
            $course['offerings']['st_jerome_only'] = true;
          }
          elseif(strpos($data, 'Also offered at St. Jerome\'s University') === 0)
          {
            $course['offerings']['st_jerome'] = true;
          }
          elseif(strpos($data, 'Department Consent Required') === 0)
          {
            $course['needs_department_consent'] = true;
          }
          elseif(strpos($data, 'Offered at Renison College') === 0 || strpos($data, 'Offered at Renison University College') === 0)
          {
            $course['offerings']['renison_only'] = true;
          }
          elseif(strpos($data, 'Also offered at Renison College') === 0 || strpos($data, 'Also offered at Renison University College') === 0)
          {
            $course['offerings']['renison'] = true;
          }
          elseif(strpos($data, 'Offered at Conrad Grebel University College') === 0)
          {
            $course['offerings']['conrad_grebel_only'] = true;
          }
          elseif(strpos($data, 'Also offered at Conrad Grebel University College') === 0)
          {
            $course['offerings']['conrad_grebel'] = true;
          }
          elseif(strpos($data, 'Instructor Consent Required') === 0)
          {
            $course['needs_instructor_consent'] = true;
          }
          elseif(strpos($data, '[Note:') === 0)
          {
            $course['notes'] = $data;
          }
          else
          {
            $extra_fields[] = $data;
          }
        }

        next($tr);
      }

      if($extra_fields)
      {
        $course['extra'] = $extra_fields;
      }

      $terms_data = NULL;

      if(strpos($course['description'], 'Offered:') !== false )
      {
        $terms_data = trim(end(explode('Offered:', $course['description'])), "()[],;. ");
      }

      if(strpos($course['notes'], 'Offered:') !== false )
      {
        $terms_data = trim(end(explode('Offered:', $course['notes'])), "()[],;. ");
      }

      $course['terms_offered'] = ($terms_data != NULL) ? explode(',', $terms_data) : array();

      $courses[] = $course;
    }
  }

  return $courses;
}


function sanitize($input) {
  return mb_convert_encoding($input, 'UTF-8', mb_detect_encoding($input, 'UTF-8, ISO-8859-1', true));
}



$subject = "MATH";
$courses = parse_courses($subject);

print_r($courses);



?>
