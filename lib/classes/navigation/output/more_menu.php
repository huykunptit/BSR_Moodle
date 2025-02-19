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
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace core\navigation\output;

use context_system;
use renderable;
use renderer_base;
use templatable;
use custom_menu;

/**
 * more menu navigation renderable
 *
 * @package     core
 * @category    navigation
 * @copyright   2021 onwards Adrian Greeve
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class more_menu implements renderable, templatable {

    protected $content;
    protected $navbarstyle;
    protected $haschildren;
    protected $istablist;

    /**
     * Constructor for this class.
     *
     * @param object $content Navigation objects.
     * @param string $navbarstyle class name.
     * @param bool $haschildren The content has children.
     * @param bool $istablist When true, the more menu should be rendered and behave with a tablist ARIA role.
     *                        If false, it's rendered with a menubar ARIA role. Defaults to false.
     */
    public function __construct(object $content, string $navbarstyle, bool $haschildren = true, bool $istablist = false) {
        $this->content = $content;
        $this->navbarstyle = $navbarstyle;
        $this->haschildren = $haschildren;
        $this->istablist = $istablist;
    }

    /**
     * Return data for rendering a template.
     *
     * @param renderer_base $output The output
     * @return array Data for rendering a template
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function export_for_template(renderer_base $output): array {
        global $USER, $DB;
    
        // Initializing the data array with some properties.
        $data = [
            'navbarstyle' => $this->navbarstyle,
            'istablist' => $this->istablist,
        ];
    
        // Fetch the current user's ID and role.
        $userid = $USER->id;
        $roleid = $DB->get_field('role_assignments', 'roleid', ['userid' => $userid]);
    
        // Check if the current node has children.
        if ($this->haschildren) {
    
            // If no children are found, return an empty array.
            if (!isset($this->content->children) || count($this->content->children) == 0) {
                return [];
            }
    
            // Iterate through children and set 'moremenuid' and 'haschildren' if necessary.
            foreach ($this->content->children as &$item) {
                if ($item->showchildreninsubmenu && isset($this->content->children) && count($this->content->children) > 0) {
                    $item->moremenuid = uniqid();
                    $item->haschildren = true;
                }
            }
    
            // Assign the node collection with children to the data array.
            $data['nodecollection'] = $this->content;
    
        } else {
            // Check if the user is an admin (roleid = 102) and fetch admin role if present.
            $roleid_admin = $DB->get_field('role_assignments', 'roleid', ['userid' => $userid, 'roleid' => 102]);
            // dd($roleid_admin,$roleid);
            // If the user is a regular user (roleid = 5) or doesn't have a role.
            if ($roleid == 5) {
                // If no admin role is found, limit content based on conditions.
            
                // Convert content to array and initialize nodearray.
                $contentArray = (array) $this->content;
                $data['nodearray'] = [];
            
                // Add first element if it exists.
                if (isset($contentArray[0])) {
                    $data['nodearray'][] = $contentArray[0];
                }
            
                // Modify text of the 5th element and add it if it exists.
                if (isset($contentArray[5])) {
                    $contentArray[5]->text = 'Kỳ thi của tôi';
                    $data['nodearray'][] = $contentArray[5];
                }
            
                // Add the 6th element if it exists.
                if (isset($contentArray[6])) {
                    $data['nodearray'][] = $contentArray[6];
                }
            
            } else if (($roleid == 102 || $roleid != 5) && $roleid !=1) {
                
                // If admin role exists, add a slice of content and remove the second element.
                $data['nodearray'] = array_slice((array) $this->content, 0, 5);
                unset($data['nodearray'][1]);
            
                // Re-index the array to keep continuous numeric keys.
                $data['nodearray'] = array_values($data['nodearray']);
            
            } else if ($roleid == 1) {
                // If the role is not 5 or admin, return the first 5 elements of content.
                $data['nodearray'] = array_slice((array) $this->content, 0, 5);
            }
            
        }
    
        // Assign a unique ID for moremenuid.
        $data['moremenuid'] = uniqid();
    
        return $data;
    }
    



}
