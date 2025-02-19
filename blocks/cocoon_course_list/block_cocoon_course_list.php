<?php

include_once $CFG->dirroot . "/course/lib.php";
include_once $CFG->dirroot . "/blocks/moodleblock.class.php";
require_once $CFG->dirroot .
    "/theme/edumy/ccn/block_handler/ccn_block_handler.php";
use qbank_managecategories\helper;

class block_cocoon_course_list extends block_list
{
    /** @var array of contexts. */
    protected $contexts;

    function init()
    {
        $this->title = get_string("pluginname", "block_cocoon_course_list");
    }
    function has_config()
    {
        return true;
    }

    function applicable_formats()
    {
        $ccnBlockHandler = new ccnBlockHandler();
        return $ccnBlockHandler->ccnGetBlockApplicability(["all"]);
    }

    function get_content()
    {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE, $COURSE, $category;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        // dd($this->context);
        $this->content->items = [];
        $this->content->icons = [];
        $this->content->footer = "";
        if (!empty($this->config->title)) {
            $this->content->title = $this->config->title;
        }
        if (!empty($this->config->child_categories)) {
            $this->content->child_categories = $this->config->child_categories;
        } else {
            $this->content->child_categories = "0";
        }
        
        $question_count = $DB->count_records("question");
        $name = get_string("questioncategory", "question");
        
        // dd($COURSE->id);
        $this->content->footer =
            '
            <div class="selected_filter_widget style2  m-10 w-25" style=" padding: 0.5rem;
            border:1px solid #1e4fa5; 
            border-radius: 1rem;
            float:left;width: 40%; margin-right: 30px;">
                <h2 class="mb-3" style="color:#1e4fa5;">' .
            $name .
            '</h2>
                <div>
        ';

        // $url = "/question/edit1.php?courseid={$COURSE->id}&deleteall=1&category={$categoryParamValue}&searchtext=&qbshowtext=1&recurse=0&recurse=1&showhidden=0&showhidden=1";
        $topcategory = core_course_category::top();
        // $categories = $topcategory->get_children();

        global $DB;
        $sql = "SELECT c.id AS course_id, cc.id AS category_id, cc.name AS category_name
        FROM {course} c
        JOIN {course_categories} cc ON c.category = cc.id
        WHERE   cc.isquestionbank = 1"; //

        try {
            // $results = $DB->get_records_sql('SELECT * FROM {course_categories} WHERE isquestionbank = 1');

            $results = $DB->get_records_sql($sql);
            // dd($results);
            // dd($results);
        } catch (dml_exception $e) {
            dd("Error: " . $e->getMessage());
        }

        // dd($results);
        //         $catcontext = context_coursecat::instance($category->id);
        //         $rec = $DB->get_record_sql("SELECT SUM(temptable.questioncount) as questioncount
        //  					FROM
        // (						SELECT id, (SELECT count(1) FROM {question} q
        //                         WHERE c.id = q.category AND q.hidden='0' AND q.parent='0') AS questioncount
        //              			FROM {question_categories} c
        //              			WHERE c.contextid=?)temptable", array($catcontext->id));

        $course_ids = [];
        $category_names = [];

        $first_course_ids = [];
        
        foreach ($results as $result) {
           
            if (!isset($first_course_ids[$result->category_id])) {
                $first_course_ids[$result->category_name] = $result->course_id;
                // 
                
            }
        }
        $collator = new Collator("vi_VN");
        uksort($first_course_ids, function ($a, $b) use ($collator) {
            return $collator->compare($a, $b);
        });
    
        if (
            $topcategory->is_uservisible() &&
            ($categories = $topcategory->get_children())
        ) {
            
            foreach ($first_course_ids as $category_name => $course_id) {
                $context = context_course::instance($course_id);

                $category_id = $DB->get_field("course", "category", [
                    "id" => $course_id,
                ]);

                $cat = core_course_category::get($category_id);
                if(!$cat->can_create_course()) {
                    continue;
                }
                
                $course_cat = $DB->get_record("context", [
                    "contextlevel" => 40,
                    "instanceid" => $category_id,
                ]);

                
                $question_groups = $DB->get_records("question_categories", [
                    "contextid" => $course_cat->id,
                ]);
                // dump($course_cat->id);
                $filtered_question_groups = array_filter($question_groups, function($item) {
                    return $item->name !== 'top';
                });
                // dump( $filtered_question_groups);
                $question_group_ids = [];
                foreach ($filtered_question_groups as $question_group) {
                    $question_group_ids[] = $question_group->id;
                   
                    // $question_group_ids = array_slice(
                    //     $question_group_ids,
                    //     0,
                    //     1
                    // );
                }
                
                list($sql_in, $params) = $DB->get_in_or_equal(
                    $question_group_ids
                );
                $sql = "SELECT qbe.*, q.*
                FROM {question_bank_entries} qbe
                JOIN {question_versions} qv ON qbe.id = qv.questionbankentryid
                JOIN {question} q ON qv.questionid = q.id
                WHERE q.parent = 0 AND qv.status = 'ready' AND qbe.questioncategoryid $sql_in";
                $question_bank_entries = $DB->get_records_sql($sql, $params);
                
                $question_count = count($question_bank_entries);
                // dump($question_count);
                
                $this->content->footer .=
                    '
                    <div class="panel">
                        <div class="panel-heading">
                            <p class="panel-title" style="margin: 0; display: flex;">
                                <a href="#panelBody' .
                    $category->id .
                    '" class="link mb-2 d-flex align-items-center justify-content-between" data-toggle="collapse">
                                    <span style="font-size:14px">
                                    <a href="' . $CFG->wwwroot . '/question/edit1.php?courseid=' . $course_id . '&cat=' . $question_group->id . '%2C' . $course_cat->id . '&qperpage=50&searchtext=&recurse=1&showhidden=1&qbshowtext=1">' . $category_name . '</a>

                                    </span>
                                    <span style="margin-left: auto;">' .
                    $question_count .
                    '</span>
                                </a>
                            </p>
                        </div>
                        
                               
                ';

                // foreach ($courses as $course) {
                //     $this->content->footer .= '
                //         <li style="margin-left: 20px;">
                //             <i class="fa fa-file"></i> <a href="' . $CFG->wwwroot . '/question/edit1.php?courseid=' . $course->id . '">' . $course->fullname . '</a>

                //         </li>
                //     ';
                // }

                $this->content->footer .= '
                               
                       
                    </div>
                ';
            }
        }

        $this->content->footer .= '
            </div>
        </div>';

        return $this->content;
    }

    /**
     * Returns the role that best describes the course list block.
     *
     * @return string
     */
    public function get_aria_role()
    {
        return "navigation";
    }
    public function html_attributes()
    {
        global $CFG;
        $attributes = parent::html_attributes();
        include $CFG->dirroot . "/theme/edumy/ccn/block_handler/attributes.php";
        return $attributes;
    }
}
