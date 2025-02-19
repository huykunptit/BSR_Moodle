<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public  License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
global $CFG;

/**
 * Contains renderers for the course category question view
 *
 * @package core_course
 * @copyright 2013 Sam Hemelryk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

   // Kiểm tra xem đường dẫn in ra có đúng không

require_once($CFG->dirroot.'/lib/coursecatlib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot . '/question/editlib.php');

class coursecat_question_manager{
	
	
	/**
	 * Initialises the JS required to enhance the management interface.
	 *
	 * Thunderbirds are go, this function kicks into gear the JS that makes the
	 * course management pages that much cooler.
	 */
	public function enhance_management_interface() {
		
	}
	
	
	/**
	 * Prepares the form element for the course category listing bulk actions.
	 *
	 * @return string
	 */
	public function management_form_start() {
		
		global $PAGE;
		
		$form = array('action' => $PAGE->url->out(), 'method' => 'POST', 'id' => 'coursecat-question-management');
	
		$html = html_writer::start_tag('form', $form);
		$html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
		return $html;
	}
	
	/**
	 * Closes the course category bulk management form.
	 *
	 * @return string
	 */
	public function management_form_end() {
		return html_writer::end_tag('form');
	}
	
	
	public function grid_start($id = null, $class = null) {
		$gridclass = 'grid-row-r row-fluid';
		if (is_null($class)) {
			$class = $gridclass;
		} else {
			$class .= ' ' . $gridclass;
		}
		$attributes = array();
		if (!is_null($id)) {
			$attributes['id'] = $id;
		}
		return html_writer::start_div($class, $attributes);
	}
	
	public function grid_end() {
		return html_writer::end_div();
	}
	
	/**
	 * Opens a grid column
	 *
	 * @param int $size The number of segments this column should span.
	 * @param string $id An id to give the column.
	 * @param string $class A class to give the column.
	 * @return string
	 */
	public function grid_column_start($size, $id = null, $class = null) {
	
		// Calculate Bootstrap grid sizing.
		$bootstrapclass = 'span'.$size;
	
		// Calculate YUI grid sizing.
		if ($size === 12) {
			$maxsize = 1;
			$size = 1;
		} else {
			$maxsize = 12;
			$divisors = array(8, 6, 5, 4, 3, 2);
			foreach ($divisors as $divisor) {
				if (($maxsize % $divisor === 0) && ($size % $divisor === 0)) {
					$maxsize = $maxsize / $divisor;
					$size = $size / $divisor;
					break;
				}
			}
		}
		if ($maxsize > 1) {
			$yuigridclass =  "grid-col-{$size}-{$maxsize} grid-col";
		} else {
			$yuigridclass =  "grid-col-1 grid-col";
		}
	
		if (is_null($class)) {
			$class = $yuigridclass . ' ' . $bootstrapclass;
		} else {
			$class .= ' ' . $yuigridclass . ' ' . $bootstrapclass;
		}
		$attributes = array();
		if (!is_null($id)) {
			$attributes['id'] = $id;
		}
		return html_writer::start_div($class, $attributes);
	}
	
	/**
	 * Closes a grid column.
	 *
	 * @return string
	 */
	public function grid_column_end() {
		return html_writer::end_div();
	}
	
	
	public function render_coursecat_question_bank($category){
		
		$categorysize = 4;
		$questionsize = 8;
		
		
		$class = 'columns-2';
		
		$class .= ' viewmode-cobmined';
		
		$output = '';
		
		$this->enhance_management_interface();
		
		//echo $this->management_heading(get_string('disciplinequestionbank'));
		
		echo $this->management_form_start();
		
		echo $this->grid_start('course-category-listings', $class);
		
		
		// start first column
		echo $this->grid_column_start($categorysize, 'qb-category-listing');
		
		
		
		
		echo $this->category_listing($category);
		
		
		echo $this->grid_column_end();
		// end of the first column
		
		// start second column
		
		echo $this->grid_column_start($questionsize, 'question-listing');
		
		$this->question_bank_listing($category);
		
		echo $this->grid_column_end();
		// end of second column
		
		echo $this->grid_end();
		
		echo $this->management_form_end();
		
		
		
		 
	}
	
	
	public function management_heading($heading) {
		$html = html_writer::start_div('coursecat-management-header clearfix');
		$html .= '<h2>'.$heading.'</h2>';
		$html .= html_writer::end_div();
		return $html;
	}
	
	public function question_bank_listing(coursecat $category = null) {
	
		$listing = coursecat::get(0)->get_children();
		
		if(!$category){
			$category = $listing[0];
		}
		
		
		echo html_writer::start_div('question-listing');
		
		$param = '';
		if($category){
			$param = '?categoryid=' . $category->id;
		}
		list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) =
		question_edit_setup('questions', '/question/coursecatquestview.php'.$param, false, false, false);
		
		global $COURSE;
		$questionbank = new core_question\bank\view($contexts, $thispageurl, $COURSE, $cm);
		$questionbank->process_actions();
		
		
		echo '<div class="questionbankwindow">';
		
		$questionbank->display('questions', $pagevars['qpage'], $pagevars['qperpage'],
				$pagevars['cat'], $pagevars['recurse'], false, $pagevars['qbshowtext']);
		
		
		echo "</div>\n";
				
		echo html_writer::end_div();

	}
		
	public function category_listing(coursecat $category = null) {
	
		$listing = coursecat::get(0)->get_children();
		
		if(!$category){
			$category = $listing[0];
		}
		
		
		if ($category === null) {
			$selectedparents = array();
			$selectedcategory = null;
		} else {
			$selectedparents = $category->get_parents();
			$selectedparents[] = $category->id;
			$selectedcategory = $category->id;
		}
		$catatlevel = \core_course\management\helper::get_expanded_categories('');
		$catatlevel[] = array_shift($selectedparents);
		$catatlevel = array_unique($catatlevel);
	
		
	
		$attributes = array(
				'class' => 'ml',
				'role' => 'tree',
				'aria-labelledby' => 'category-listing-title'
		);
	
		$html  = html_writer::start_div('category-listing');
		$html .= html_writer::tag('h3', get_string('categories'), array('id' => 'category-listing-title'));
		//$html .= $this->category_listing_actions($category);
		$html .= html_writer::start_tag('ul', $attributes);
		foreach ($listing as $listitem) {
			
			if(!($listitem->isquestionbank > 0)){
				continue;
			}
			
			$cccontext = context_coursecat::instance($listitem->id);
			 
			 
			if ((!has_any_capability(question_edit_contexts::$caps['questions'], $cccontext))||((!has_capability('moodle/category:view', $cccontext)))){
				continue;
			}
			// Render each category in the listing.
			
			$children_count = 0;
			$subcategories = array();
			$is_in_cat = false;
			
			if (in_array($listitem->id, $catatlevel)) {
				$is_in_cat = true;
			}
			
			
			$tempsubcategories = $listitem->get_children();
			
			if(!empty($tempsubcategories)){
			
				foreach ($tempsubcategories as $templistitem){
					if(($templistitem->isquestionbank > 0)){
						
						if($is_in_cat){
							$subcategories[] = $templistitem;
						}
						
						$children_count ++;
			
					}
				}
			}
			
			$html .= $this->category_listitem(
					$listitem,
					$subcategories,
					$children_count,
					$selectedcategory,
					$selectedparents
			);
		}
		$html .= html_writer::end_tag('ul');
		$html .= html_writer::end_div();
		return $html;
	}
	
	
	/**
	 * Renders a category list item.
	 *
	 * This function gets called recursively to render sub categories.
	 *
	 * @param coursecat $category The category to render as listitem.
	 * @param coursecat[] $subcategories The subcategories belonging to the category being rented.
	 * @param int $totalsubcategories The total number of sub categories.
	 * @param int $selectedcategory The currently selected category
	 * @param int[] $selectedcategories The path to the selected category and its ID.
	 * @return string
	 */
	public function category_listitem(coursecat $category, array $subcategories, $totalsubcategories,
			$selectedcategory = null, $selectedcategories = array()) {
	
		global $OUTPUT;
		
		$isexpandable = ($totalsubcategories > 0);
		$isexpanded = (!empty($subcategories));
		
		$activecategory = ($selectedcategory === $category->id);
		$attributes = array(
				'class' => 'listitem listitem-category',
				'data-id' => $category->id,
				'data-expandable' => $isexpandable ? '1' : '0',
				'data-expanded' => $isexpanded ? '1' : '0',
				'data-selected' => $activecategory ? '1' : '0',
				'data-visible' => $category->visible ? '1' : '0',
				'role' => 'treeitem',
				'aria-expanded' => $isexpanded ? 'true' : 'false'
		);
		$text = $category->get_formatted_name();
		if ($category->parent) {
			$a = new stdClass;
			$a->category = $text;
			$a->parentcategory = $category->get_parent_coursecat()->get_formatted_name();
			$textlabel = get_string('categorysubcategoryof', 'moodle', $a);
		}
		$courseicon = $OUTPUT->pix_icon('i/course', get_string('questions'));
		$bcatinput = array(
				'type' => 'checkbox',
				'name' => 'bcat[]',
				'value' => $category->id,
				'class' => 'bulk-action-checkbox',
				'aria-label' => get_string('bulkactionselect', 'moodle', $text),
				'data-action' => 'select'
		);
	
		$bcatinput['style'] = 'visibility:hidden';
	
		$viewcaturl = new moodle_url('/question/coursecatquestview.php', array('categoryid' => $category->id));
		if ($isexpanded) {
			$icon = $OUTPUT->pix_icon('t/switch_minus', get_string('collapse'), 'moodle', array('class' => 'tree-icon', 'title' => ''));
			$icon = html_writer::link(
					$viewcaturl,
					$icon,
					array(
							'class' => 'float-left',
							'data-action' => 'collapse',
							'title' => get_string('collapsecategory', 'moodle', $text),
							'aria-controls' => 'subcategoryof'.$category->id
					)
			);
		} else if ($isexpandable) {
			$icon = $OUTPUT->pix_icon('t/switch_plus', get_string('expand'), 'moodle', array('class' => 'tree-icon', 'title' => ''));
			$icon = html_writer::link(
					$viewcaturl,
					$icon,
					array(
							'class' => 'float-left',
							'data-action' => 'expand',
							'title' => get_string('expandcategory', 'moodle', $text)
					)
			);
		} else {
			$icon = $OUTPUT->pix_icon(
					'i/navigationitem',
					'',
					'moodle',
					array('class' => 'tree-icon', 'title' => get_string('showcategory', 'moodle', $text))
			);
			$icon = html_writer::span($icon, 'float-left');
		}

	
		$html = html_writer::start_tag('li', $attributes);
		$html .= html_writer::start_div('clearfix');
		$html .= html_writer::start_div('float-left ba-checkbox');
		$html .= html_writer::empty_tag('input', $bcatinput).'&nbsp;';
		$html .= html_writer::end_div();
		$html .= $icon;
		
		$textattributes = array('class' => 'float-left categoryname without-actions');
		
		if (isset($textlabel)) {
			$textattributes['aria-label'] = $textlabel;
		}
		$html .= html_writer::link($viewcaturl, $text, $textattributes);
		$html .= html_writer::start_div('float-right');
		if ($category->idnumber) {
			$html .= html_writer::tag('span', s($category->idnumber), array('class' => 'dimmed idnumber'));
		}

		//$category->get_courses_count()
		
		////
		global $DB;
		$catcontext = context_coursecat::instance($category->id);
		
		$rec = $DB->get_record_sql("SELECT SUM(temptable.questioncount) as questioncount 
 					FROM
(						SELECT id, (SELECT count(1) FROM {question} q
                        WHERE c.id = q.category AND q.hidden='0' AND q.parent='0') AS questioncount
             			FROM {question_categories} c
             			WHERE c.contextid=?)temptable", array($catcontext->id));
		
		
		
		
		//
		
		$question_count = 0;
		
		if($rec){
			$question_count = $rec->questioncount;
			
		}
		
		$countid = 'course-count-'.$category->id;
		$html .= html_writer::span(
				' '. html_writer::span($question_count) .
				html_writer::span(get_string('questions'), 'accesshide', array('id' => $countid)),
				'course-count',
				array('aria-labelledby' => $countid)
		);
		$html .= html_writer::end_div();
		$html .= html_writer::end_div();
		if ($isexpanded) {
			$html .= html_writer::start_tag('ul',
					array('class' => 'ml', 'role' => 'group', 'id' => 'subcategoryof'.$category->id));
			$catatlevel = \core_course\management\helper::get_expanded_categories($category->path);
			$catatlevel[] = array_shift($selectedcategories);
			$catatlevel = array_unique($catatlevel);
			foreach ($subcategories as $listitem) {
				$temptchildcategories = (in_array($listitem->id, $catatlevel)) ? $listitem->get_children() : array();
				
				if(!($listitem->isquestionbank > 0)){
					continue;
				}
				
				$children_count = 0;
				$childcategories = array();
					
				$tempsubcategories = $temptchildcategories;
					
				if(!empty($tempsubcategories)){
						
					foreach ($tempsubcategories as $templistitem){
						
						if(($templistitem->isquestionbank > 0)){				
							$childcategories[] = $templistitem;
							$children_count ++;
								
						}
					}
				}
				
				$html .= $this->category_listitem(
						$listitem,
						$childcategories,
						$children_count,
						$selectedcategory,
						$selectedcategories
				);
			}
			$html .= html_writer::end_tag('ul');
		}
		$html .= html_writer::end_tag('li');
		return $html;
	}
	
}
