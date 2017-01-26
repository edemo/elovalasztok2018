<?php
// elovalasztok2018 OEVK -k létrehozása
// JUMI -ban futtatható

$category_data['id'] = 0;
$category_data['parent_id'] = 0;
$category_data['title'] = 'My Category Title';
$category_data['alias'] = '';
$category_data['extension'] = 'com_content';
$category_data['published'] = 1;
$category_data['language'] = '*';
$category_data['params'] = array('category_layout' => '','image' => '');
$category_data['metadata'] = array('author' => '','robots' => '');


function createCategory( $data )
{
    $data['rules'] = array(
        'core.edit.state' => array(),
        'core.edit.delete' => array(),
        'core.edit.edit' => array(),
        'core.edit.state' => array(),
        'core.edit.own' => array(1=>true)
    );

    $basePath = JPATH_ADMINISTRATOR.'/components/com_categories';
    require_once $basePath.'/models/category.php';
    $config  = array('table_path' => $basePath.'/tables');
    $category_model = new CategoriesModelCategory($config);
    if(!$category_model->save($data))
	{
        $err_msg = $category_model->getError();
		echo $err_msg;
        return false;
    }else{
        $id = $category_model->getItem()->id;
        return $id;
    }
}


function myCategoryCreate(&$category_data, $parent, $title, $alias = '') 
{
  if ($alias == '')
  {
	  $alias = str_replace(' ','-',$title);
	  $alias = str_replace('.','',$alias);
	  $alias = str_replace('á','a',$alias);
	  $alias = str_replace('é','e',$alias);
	  $alias = str_replace('ü','u',$alias);
	  $alias = str_replace('ö','o',$alias);
	  $alias = str_replace('ó','o',$alias);
	  
  }
  $category_data['id'] = 0;
  $category_data['parent_id'] = $parent;
  $category_data['title'] = $title;
  $category_data['alias'] = $alias;
  $category_id = createCategory($category_data);
	
  echo $title.' '.$category_id.	'íbr>';
	
  return $category_id;	
}
// $parent = myCategoryCreate($category_data, 0, 'Országos választó kerületek');
$parent = 8;
myCategoryCreate($category_data, $parent, 'Bács-Kiskun 01.');
myCategoryCreate($category_data, $parent, 'Bács-Kiskun 02.');
myCategoryCreate($category_data, $parent, 'Bács-Kiskun 03.');
myCategoryCreate($category_data, $parent, 'Bács-Kiskun 04.');
myCategoryCreate($category_data, $parent, 'Bács-Kiskun 05.');
myCategoryCreate($category_data, $parent, 'Bács-Kiskun 06.');
myCategoryCreate($category_data, $parent, 'Baranya 01.');
myCategoryCreate($category_data, $parent, 'Baranya 02.');
myCategoryCreate($category_data, $parent, 'Baranya 03.');
myCategoryCreate($category_data, $parent, 'Baranya 04.');
myCategoryCreate($category_data, $parent, 'BAZ 01.');
myCategoryCreate($category_data, $parent, 'BAZ 02.');
myCategoryCreate($category_data, $parent, 'BAZ 03.');
myCategoryCreate($category_data, $parent, 'BAZ 02.');
myCategoryCreate($category_data, $parent, 'BAZ 05.');
myCategoryCreate($category_data, $parent, 'BAZ 06.');
myCategoryCreate($category_data, $parent, 'BAZ 07.');
myCategoryCreate($category_data, $parent, 'Békés 01.');
myCategoryCreate($category_data, $parent, 'Békés 02.');
myCategoryCreate($category_data, $parent, 'Békés 03.');
myCategoryCreate($category_data, $parent, 'Békés 04.');
myCategoryCreate($category_data, $parent, 'Budapest 01.');
myCategoryCreate($category_data, $parent, 'Budapest 02.');
myCategoryCreate($category_data, $parent, 'Budapest 03.');
myCategoryCreate($category_data, $parent, 'Budapest 04.');
myCategoryCreate($category_data, $parent, 'Budapest 05.');
myCategoryCreate($category_data, $parent, 'Budapest 06.');
myCategoryCreate($category_data, $parent, 'Budapest 07.');
myCategoryCreate($category_data, $parent, 'Budapest 08.');
myCategoryCreate($category_data, $parent, 'Budapest 09.');
myCategoryCreate($category_data, $parent, 'Budapest 10.');
myCategoryCreate($category_data, $parent, 'Budapest 11.');
myCategoryCreate($category_data, $parent, 'Budapest 12.');
myCategoryCreate($category_data, $parent, 'Budapest 13.');
myCategoryCreate($category_data, $parent, 'Budapest 14.');
myCategoryCreate($category_data, $parent, 'Budapest 15.');
myCategoryCreate($category_data, $parent, 'Budapest 16.');
myCategoryCreate($category_data, $parent, 'Budapest 17.');
myCategoryCreate($category_data, $parent, 'Budapest 18.');
myCategoryCreate($category_data, $parent, 'Csongrád 01.');
myCategoryCreate($category_data, $parent, 'Csongrád 02.');
myCategoryCreate($category_data, $parent, 'Csongrád 03.');
myCategoryCreate($category_data, $parent, 'Csongrád 04.');
myCategoryCreate($category_data, $parent, 'Fejér 01.');
myCategoryCreate($category_data, $parent, 'Fejér 02.');
myCategoryCreate($category_data, $parent, 'Fejér 03.');
myCategoryCreate($category_data, $parent, 'Fejér 04.');
myCategoryCreate($category_data, $parent, 'Fejér 05.');
myCategoryCreate($category_data, $parent, 'GYMS 01.');
myCategoryCreate($category_data, $parent, 'GYMS 02.');
myCategoryCreate($category_data, $parent, 'GYMS 03.');
myCategoryCreate($category_data, $parent, 'GYMS 04.');
myCategoryCreate($category_data, $parent, 'GYMS 05.');
myCategoryCreate($category_data, $parent, 'Hajdu 01.');
myCategoryCreate($category_data, $parent, 'Hajdu 02.');
myCategoryCreate($category_data, $parent, 'Hajdu 03.');
myCategoryCreate($category_data, $parent, 'Hajdu 04.');
myCategoryCreate($category_data, $parent, 'Hajdu 05.');
myCategoryCreate($category_data, $parent, 'Hajdu 06.');
myCategoryCreate($category_data, $parent, 'Heves 01.');
myCategoryCreate($category_data, $parent, 'Heves 02.');
myCategoryCreate($category_data, $parent, 'Heves 03.');
myCategoryCreate($category_data, $parent, 'JNSZ 01.');
myCategoryCreate($category_data, $parent, 'JNSZ 02.');
myCategoryCreate($category_data, $parent, 'JNSZ 03.');
myCategoryCreate($category_data, $parent, 'JNSZ 04.');
myCategoryCreate($category_data, $parent, 'Komárom 01.');
myCategoryCreate($category_data, $parent, 'Komárom 02.');
myCategoryCreate($category_data, $parent, 'Komárom 03.');
myCategoryCreate($category_data, $parent, 'Kukutyin 01.');
myCategoryCreate($category_data, $parent, 'Nógrád 01.');
myCategoryCreate($category_data, $parent, 'Nógrád 02.');
myCategoryCreate($category_data, $parent, 'Pest 01.');
myCategoryCreate($category_data, $parent, 'Pest 02.');
myCategoryCreate($category_data, $parent, 'Pest 03.');
myCategoryCreate($category_data, $parent, 'Pest 04.');
myCategoryCreate($category_data, $parent, 'Pest 05.');
myCategoryCreate($category_data, $parent, 'Pest 06.');
myCategoryCreate($category_data, $parent, 'Pest 07.');
myCategoryCreate($category_data, $parent, 'Pest 08.');
myCategoryCreate($category_data, $parent, 'Pest 09.');
myCategoryCreate($category_data, $parent, 'Pest 10.');
myCategoryCreate($category_data, $parent, 'Pest 11.');
myCategoryCreate($category_data, $parent, 'Pest 12.');
myCategoryCreate($category_data, $parent, 'Somogy 01.');
myCategoryCreate($category_data, $parent, 'Somogy 02.');
myCategoryCreate($category_data, $parent, 'Somogy 03.');
myCategoryCreate($category_data, $parent, 'Somogy 04.');
myCategoryCreate($category_data, $parent, 'SZSZB 01.');
myCategoryCreate($category_data, $parent, 'SZSZB 02.');
myCategoryCreate($category_data, $parent, 'SZSZB 03.');
myCategoryCreate($category_data, $parent, 'SZSZB 04.');
myCategoryCreate($category_data, $parent, 'SZSZB 05.');
myCategoryCreate($category_data, $parent, 'SZSZB 06.');
myCategoryCreate($category_data, $parent, 'Tolna 01.');
myCategoryCreate($category_data, $parent, 'Tolna 02.');
myCategoryCreate($category_data, $parent, 'Tolna 03.');
myCategoryCreate($category_data, $parent, 'Vas 01.');
myCategoryCreate($category_data, $parent, 'Vas 02.');
myCategoryCreate($category_data, $parent, 'Vas 03.');
myCategoryCreate($category_data, $parent, 'Veszprém 01.');
myCategoryCreate($category_data, $parent, 'Veszprém 02.');
myCategoryCreate($category_data, $parent, 'Veszprém 03.');
myCategoryCreate($category_data, $parent, 'Veszprém 04.');
myCategoryCreate($category_data, $parent, 'Zala 01.');
myCategoryCreate($category_data, $parent, 'Zala 02.');
myCategoryCreate($category_data, $parent, 'Zala 03.');

?>