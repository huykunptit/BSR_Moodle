<?php

use core_customfield\category;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/course/renderer.php');
require_once($CFG->dirroot. '/theme/edumy/ccn/course_handler/ccn_course_handler.php');
require_once($CFG->dirroot. '/theme/edumy/ccn/block_handler/ccn_block_handler.php');
class block_cocoon_my_courses extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_cocoon_my_courses');
    }
    
    public function get_content() {
      global $CFG, $PAGE, $USER, $DB;
  
      if (isset($PAGE->theme->settings->course_enrolment_payment) && ($PAGE->theme->settings->course_enrolment_payment == 1)) {
          $paymentForced = false;
      } else {
          $paymentForced = true;
      }
  
      if ($this->content !== null) {
          return $this->content;
      }
  
      if (empty($this->instance)) {
          $this->content = '';
          return $this->content;
      }
  
      $this->content = new stdClass();
      $this->content->items = array();
      $this->content->icons = array();
      $this->content->footer = '';
      $this->content->text = '';
  
      if (isloggedin() && !isguestuser()) {
          if (!empty($this->config->title)) { $this->content->title = $this->config->title; }
          if (!empty($this->config->subtitle)) { $this->content->subtitle = $this->config->subtitle; }
          if (!empty($this->config->button_text)) { $this->content->button_text = $this->config->button_text; }
          if (!empty($this->config->button_link)) { $this->content->button_link = $this->config->button_link; }
          if (!empty($this->config->hover_text)) { $this->content->hover_text = $this->config->hover_text; }
          if (!empty($this->config->hover_accent)) { $this->content->hover_accent = $this->config->hover_accent; }
          if (!empty($this->config->description)) { $this->content->description = $this->config->description; }
          if (!empty($this->config->course_image)) { $this->content->course_image = $this->config->course_image; }
          if (!empty($this->config->price)) { $this->content->price = $this->config->price; }
          if (!empty($this->config->enrol_btn)) { $this->content->enrol_btn = $this->config->enrol_btn; }
          if (!empty($this->config->enrol_btn_text)) { $this->content->enrol_btn_text = $this->config->enrol_btn_text; }
  
          $ccnBlockShowDesc = isset($this->content->description) && $this->content->description != '0' ? 1 : 0;
          $ccnBlockShowImg = isset($this->content->course_image) && $this->content->course_image == '1' ? 1 : 0;
          $ccnBlockShowEnrolBtn = isset($this->content->enrol_btn) && isset($this->content->enrol_btn_text) && $this->content->enrol_btn == '1' ? 1 : 0;
          $ccnBlockShowPrice = isset($this->content->price) && $this->content->price == '1' ? 1 : 0;
  
          if ($PAGE->theme->settings->coursecat_enrolments != 1 ||
              $PAGE->theme->settings->coursecat_announcements != 1 ||
              isset($this->content->price) ||
              isset($this->content->enrol_btn_text) &&
              ($this->content->price == '1' || $this->content->enrol_btn == '1')
          ) {
              $ccnBlockShowBottomBar = 1;
              $topCoursesClass = 'ccnWithFoot';
          } else {
              $ccnBlockShowBottomBar = 0;
              $topCoursesClass = '';
          }
  
          $courses = enrol_get_all_users_courses($USER->id);
          $total_courses = count($courses);
          if ($total_courses < 2) {
              $topColumnClass = 'col-md-12';
              $col_class = 'col-md-6 offset-md-3 col-xl-4 offset-xl-4';
          } else if ($total_courses == 2) {
              $topColumnClass = 'col-sm-8 offset-sm-2 col-md-12 offset-md-0 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2';
              $col_class = 'col-md-6';
          } else if ($total_courses == 3) {
              $topColumnClass = 'col-sm-8 offset-sm-2 col-md-12 offset-md-0 col-xl-10 offset-xl-1';
              $col_class = 'col-md-6 col-lg-4';
          } else {
              $topColumnClass = 'col-xs-12';
              $col_class = 'col-md-6 col-lg-4 col-xl-3';
          }
          $output_course = [];
          $current_timestamp = time();
          // sort by startdate
          $expired_courses = [];
          foreach ($courses as $course) {
              if ($course->enddate > $current_timestamp) {
                  $output_course[] = $course;
              } else {
                  $expired_courses[] = $course;
              }
          }
  
    

   
          $this->content->text .= '
          <section class="our-courses">
              <div class="container">
                  <div class="row">
                      <div class="col-lg-6 offset-lg-3">
                          <div class="main-title text-center">';
                              if (!empty($this->content->title)) {
                                  $this->content->text .= '<h3 class="mt0" style="color:rgb(39, 110, 248)">' . format_text($this->content->title, FORMAT_HTML, array('filter' => true)) . '</h3>';
                              }
                              $this->content->text .= '
                          </div>
                      </div>
                  </div>
                  <div class="">
                      <div class="' . $topColumnClass . '">
                          <div class="row">
          ';
          $output_course = [];
          $current_timestamp = time();
          // sort by startdate
          $expired_courses = [];
          
          foreach ($courses as $course) {
              // check if $course->enddate is null, not display
               
              if ($course->startdate < $current_timestamp) {
                  $output_course[] = $course;
              } else {
                  $expired_courses[] = $course;
              }
          }
          // sort by nearest to currenttimestamp by startdate
          usort($output_course, function($a, $b) use ($current_timestamp) {
              return abs(($a->startdate - $current_timestamp)) - abs($b->startdate - $current_timestamp);
          }); 
          // dd($output_course);
        
        
          $chelper = new coursecat_helper();
          foreach ($output_course as $course) {
            
              if ($DB->record_exists('course', array('id' => $course->id))) {
                  $ccnCourseHandler = new ccnCourseHandler();
                  $ccnCourse = $ccnCourseHandler->ccnGetCourseDetails($course->id);
                 
                  $maxlength = null;
                  if (!empty($this->content->description)) {
                      switch ($this->content->description) {
                          case '7':
                              $maxlength = 500;
                              break;
                          case '6':
                              $maxlength = 350;
                              break;
                          case '5':
                              $maxlength = 200;
                              break;
                          case '4':
                              $maxlength = 150;
                              break;
                          case '3':
                              $maxlength = 100;
                              break;
                          case '2':
                              $maxlength = 100;
                              break;
                      }
                  }
                  $ccnCourseDescription = $ccnCourseHandler->ccnGetCourseDescription($course->id, $maxlength);
                // dd($ccnCourse);
                $is_check = 0;
                $course_category = $DB->get_record('course_categories', array('id' => $ccnCourse->categoryId));
                
                // dd($course_category);
                if($course_category->visible == 1 && $course->visible == 1){
                   
                    $this->content->text .= '
                    <div class="' . $col_class . '">
                        <div class="top_courses ' . $topCoursesClass . ' text-center" style="background-color: #f2f6ff; border-radius: 6px; width:100%;">
                            <div class="row bg-white mx-2 mt-2" style="place-content:center">
                                <img src="./worksheet.png" style="width: 80px; height:80px; object-fit:cover">
                            </div>
                            <a href="' . $ccnCourse->url . '">
                                <div class="details" style="background-color: #f2f6ff; border-radius: px; width:100%; border-box:solid 1px color:black;">
                                    <div class="tc_content">';
                                        $this->content->text .= $ccnCourse->ccnRender->updatedDate;
                                        $this->content->text .= $ccnCourse->ccnRender->title;
                                        $startDate = get_string('activitydate:opened', 'course');
                                        $this->content->text .= '<p class="text-dark d-inline">' . $startDate . '</p> ' . $ccnCourse->startDate . '<br>';
                                        $endDate = get_string('activitydate:closes', 'course');
                                        $this->content->text .= '<p class="text-dark d-inline">' . $endDate . '</p> ' . $ccnCourse->endDate;
                                        $this->content->text .= '
                                    </div>
                                </div>';
                                if ($ccnBlockShowBottomBar == 1) {
                                    $this->content->text .= '
                                    <div class="tc_footer bg-white" style="border-top-color:#b2b2b2;">
                                        <ul class="tc_meta text-center" style="color:#d63d43 ">' . $ccnCourse->ccnRender->enrolmentIcon . $ccnCourse->ccnRender->announcementsIcon . '</ul>
                                    </div>';
                                }
                                $this->content->text .= '
                            </a>
                        </div>
                    </div>';
                }
               
              }
          }
  
          $this->content->text .= '
                          </div>
                      </div>
                  </div>
              </div>
          </section>';
  
      } 
  
      return $this->content;
  }
  

    function applicable_formats() {
      $ccnBlockHandler = new ccnBlockHandler();
      return $ccnBlockHandler->ccnGetBlockApplicability(array('all'));
    }

    public function html_attributes() {
      global $CFG;
      $attributes = parent::html_attributes();
      include($CFG->dirroot . '/theme/edumy/ccn/block_handler/attributes.php');
      return $attributes;
    }


    public function instance_allow_multiple() {
          return false;
    }

    public function has_config() {
        return false;
    }

    public function cron() {
        return true;
    }


}
