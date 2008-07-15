<?php
class Sitemap_Controller extends Controller{


	public function index(){
	
		$sitemap=new Sitemap;

		if($sitemap->cache_render()==false)
		{
			$sitemap->add_url('http://www.example.com');
			echo $sitemap->render(TRUE);		
		}

	}

}
