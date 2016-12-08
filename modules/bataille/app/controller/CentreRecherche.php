<?php
	/**
	 * Created by PhpStorm.
	 * User: anthony
	 * Date: 08/12/2016
	 * Time: 20:44
	 */
	
	namespace modules\bataille\app\controller;
	
	
	use core\App;

	class CentreRecherche {
		
		
		
		//-------------------------- BUILDER ----------------------------------------------------------------------------//
		public function __construct() {
			
		}
		//-------------------------- END BUILDER ----------------------------------------------------------------------------//
		
		
		
		//-------------------------- GETTER ----------------------------------------------------------------------------//
		public function getAllRechercheType($type) {
			$dbc = App::getDb();

			$query = $dbc->select()->from("_bataille_centre_recherche")->where("type", "=", $type)->get();

			if ((is_array($query)) && (count($query) > 0)) {
				foreach ($query as $obj) {
					$recherche[] = [
						"niveau" => $obj->niveau,
						"recherche" => $obj->recherche
					];
				}

				return $recherche;
			}

			return 0;
		}
		//-------------------------- END GETTER ----------------------------------------------------------------------------//
		
		
		
		//-------------------------- SETTER ----------------------------------------------------------------------------//
		//-------------------------- END SETTER ----------------------------------------------------------------------------//
		
	}