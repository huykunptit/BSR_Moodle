<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/pdflib.php');

class quiz_papercopy{
	
    /**
     * @var stdClass $cm The course-module for the active quiz.
     */
    protected $cm;

    /**
     * @var stdClass $quiz The database row for the active quiz.
     */
    protected $quiz;

    /**
     * @var moodle_quiz $quizobj The quiz object for the active quiz.
     */
    protected $quizobj;

    /**
     * @var stdClass $context   The context for the current report.
     */
    protected $context;
    
    public function __construct($quiz, $cm, $course){
    	
    	$this->quiz = $quiz;
    	$this->cm = $cm;
    	$this->course = $course;
    	$this->context = context_module::instance($cm->id);
    	
    	//get a reference to the current quiz
    	$this->quizobj = $this->get_quiz_object();
    	
    	//and load the questions into memory
    	$this->quizobj->preload_questions();
    	$this->quizobj->load_questions();
    	
    }
    
    private function get_quiz_object(){
    	return new quiz($this->quiz, $this->cm, $this->course);
    }    
    public function print_quiz($correction=false){
    	
    	if (!$this->quizobj->get_questions() ) {
    		echo quiz_no_questions_message($this->quiz , $this->cm , $this->context);
    		//otherwise, if we have no action, display the index page
    	}
    	
    	$quba_id = $this->create_printable_copy();
    	
    	$usage = question_engine::load_questions_usage_by_activity($quba_id);        $usage = question_engine::load_questions_usage_by_activity($quba_id);

        //get an associative array, which indicates the questions which should be rendered
        $slots = $usage->get_slots();

        
        offlinequiz_create_pdf_question($usage, $this->quiz, $this->course->id,$this->context,$correction);

    }
    
    /**
     * Creates a single printable copy of the given quiz.
     *
     * @return  The ID of the created printable question usage.
     *
     */
    protected function create_printable_copy()
    {
    	//get a reference to the current user
    	global $USER;
    	
    	require_capability('mod/quiz:viewreports', $this->context);
    
    	//create a new usage object, which will allow us to create a psuedoquiz in the same context as the online quiz
    	$usage = question_engine::make_questions_usage_by_activity('mod_quiz', $this->context);
    
    	//and set the grading mode to "deferred feedback", the standard for paper quizzes
    	//this makes sense, since our paradigm is duriven by the idea that feedback is only offered once a paper quiz has been uploaded/graded
    	$usage->set_preferred_behaviour('deferredfeedback');
    
    	//get an array of questions in the current quiz
    	$quiz_questions = $this->quizobj->get_questions();
    

    	
    	//for each question in our online quiz
    	foreach($quiz_questions as $slot => $qdata)
    	{
    		$question = question_bank::make_question($qdata);
    
    		//add the new question instance to our new printable copy, keeping the maximum grade from the quiz
    		//TODO: respect maximum marks
    		$usage->add_question($question);
    	}
    
    	//initialize each of the questions
    	$usage->start_all_questions();
    
    	//save the usage to the database
    	question_engine::save_questions_usage_by_activity($usage);
    
    	//return the ID of the newly created questions usage
    	return $usage->get_id();
    }
    
	
	
}