<?php

class API {

	function __construct()
	{
		$this->db = new PDO(ADD_YOUR_PDO_CONNECTION_HERE);	
	}

	public function getItems()
	{
		$query = $this->db->query('SELECT * from components')->fetchAll(PDO::FETCH_OBJ);
		
		if(count($query) === 0) 
		{
			return json_encode(
				array(
					'result'  => 'invalid', 
					'message' => 'No components exist yet.'
				)
			);
		}

		return json_encode(
			array(
				'result'  => 'success',
				'message' => $query,
			)
		);
	}

	public function getItem($id)
	{
		$query = $this->db->query('SELECT * from components where id = ' . $id)->fetchAll(PDO::FETCH_OBJ);
		
		return $query;
	}
	
	public function newItem($name, $description, $url)
	{
		try
		{
			$stmt = $this->db->prepare("INSERT INTO components(name,description,url) VALUES(:field1,:field2,:field3)");
			$stmt->execute(
				array(
					':field1' => $name, 
					':field2' => $description,
					':field3' => $url
				)
			);
			
		}
		catch(Exception $e)
		{
			return json_encode(
				array(
					'result'  => 'success',
					'message' => $e->getMessage(),
				)
			);
		}
		
		return json_encode(
			array(
				'result'  => 'success',
				'message' => 'New Item Added.',
			)
		);
	}

}

$api = new API();

switch ($_GET['action']) 
{
	case 'get':
		echo $api->getItems();

		break;

	case 'get_item':
		$id = (int) $_GET['id'];
		
		$item = $api->getItem($id)[0];

		if(!empty($item))
		{
			echo "
			<div class=\"modal-header\">
          		<button type=\"button\" class=\"close\" data-dismiss=\"modal\">
          			<span aria-hidden=\"true\">×</span>
          		</button>
          		<h4 class=\"modal-title\" id=\"title\">$item->name</h4>
          	</div>
          	<div class=\"modal-body\">
				<p>$item->description</p>
			</div>
			<div class=\"modal-footer\">
				<a class=\"btn btn-primary btn-lg\" href=\"$item->url\" target=\"_blank\">
					View Website
				</a>
			</div>
			";
		}

		break;

	case 'new':
		$name 		 = $_POST['name'];
		$description = $_POST['description'];
		$url 		 = $_POST['url'];
		$error       = array();

		if(!empty($url) && !filter_var($url, FILTER_VALIDATE_URL))
		{
			$error[] = 'Invalid URL';
		}

		if(empty($name))
		{
			$error[] = 'You must enter a name.';
		}

		if(empty($description))
		{
			$error[] = 'You must enter a description.';
		}

		if(!empty($error))
		{
			echo json_encode(
				array(
					'result'  => 'invalid',
					'message' => implode("\n", $error),
				)
			);

			exit;
		}

		echo $api->newItem($name, $description, $url);

		break;
	
	default:
		echo json_encode(
			array(
				'result'  => 'invalid',
				'message' => 'No action provided.',
			)
		);

		break;
}

?>