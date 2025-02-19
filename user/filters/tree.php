<?php
class MyClass {
    // Phương thức private buildTree
    public function buildTree($departments, $parent_id = 0): array {
        $branch = [];
        foreach ($departments as $department) {
            if ($department->parent == $parent_id) {
                $children = $this->buildTree($departments, $department->id); // Sử dụng $this để gọi phương thức trong cùng một lớp
                if ($children) {
                    $department->children = $children;
                }
                $branch[$department->id] = $department;
                unset($departments[$department->id]);
            }
        }
        return $branch;
    }

    // Phương thức private displayTree
    public function displayTree($tree): void {
        echo '<div id="treeContainer" class="tree-container">';
        echo '<ul class="custom-tree" id="tree">';
        foreach ($tree as $node) {
            echo '<li>';
            echo '<input type="checkbox" checked>';
            echo '<a href="#">' . $node->name . '</a>';
            if (!empty($node->children)) {
                echo '<ul class="childen">';
                $this->displayTree($node->children); // Sử dụng $this để gọi phương thức trong cùng một lớp
                echo '</ul>';
            }
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }
}