<?php
class Zend_View_Helper_Tasks extends Zend_View_Helper_Abstract {
	
	public function tasks($tasks = null, array $config = array()) {
		if (is_null($tasks)) {
			return $this;
		}
		
		$items = array($this->head($config));
		
		foreach ($tasks as $task) {
			$items[] = $this->task($task, $config);
		}
		
		return sprintf("<table class='multirow-table' id='technic-tasks'>%s</table>", implode("", $items));
	}
	
	public function head(array $config = array()) {
		$cols = array(
				"<th>Zadal</th>",
				"<th>Zadáno</th>",
				"<th>Splnil</th>",
				"<th>Splněno</th>",
				"<th>Akce</th>"
				);
		
		$rows = array(
				sprintf("<tr>%s</tr>", implode("", $cols))
				);
		
		return sprintf("<thead>%s</thead>", implode("", $rows));
	}
	
	public function task($task, array $config = array()) {
		
		$items = array(
				sprintf("<td>%s</td>", $task->creator_name),
				sprintf("<td>%s</td>", str_replace("-", ". ", $task->created_at)),
				sprintf("<td>%s</td>", $task->completer_name ? $task->completer_name : "-"),
				sprintf("<td>%s</td>", $task->completed_at ? str_replace("-", ". ", $task->completed_at) : "-")
				);
		
		// sestaveni tlacitek
		$buttons = array(
				sprintf("<input type='hidden' name='taskId' value='%s'>", $task->id) .
				$this->generateButton("get", "Komentáře")
				);
		
		if ($config["editable"]) {
			$buttons[] = $this->generateButton("put", "Upravit");
		}
		
		if ($config["completable"]) {
			$buttons[] = sprintf("<label for='task-%s'>Vybrat</label><input type='hidden' name='task[%s]' value='0'><input type='checkbox' id='task-%s' name='task[%s]' value='1'>", $task->id, $task->id, $task->id, $task->id);
		}
		
		$items[] = sprintf("<td rowspan='3'>%s</td>", implode("<br />", $buttons));
		
		// vygenerovani radku
		$rows = array(
				sprintf("<tr>%s</tr>", implode("", $items)),
				sprintf("<tr><td colspan='4' style='white-space: pre-wrap; font-weight: bold;'>%s</td></tr>", $task->task),
				sprintf("<tr><td colspan='4' style='white-space: pre-wrap; font-style: italic; '>%s</td></tr>", $task->description)
				);
		
		return sprintf("<tbody class='%s' style='border: solid black 1px;'>%s</tbody>", $task->completed_by ? "mistake-removed" : "", implode("", $rows));
	}
	
	public function generateButton($name, $text) {
		return sprintf("<button name='%s' type='button'>%s</button>", $name, $text);
	}
}