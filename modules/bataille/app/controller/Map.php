<?php

	namespace modules\bataille\app\controller;

	use core\App;
	use core\functions\DateHeure;

	class Map {
		private $largeur;
		private $hauteur;


		//-------------------------- BUILDER ----------------------------------------------------------------------------//
		/**
		 * Map constructor.
		 * @param null $id_base
		 * @param null $install_base
		 * si id_base == null et install_base != null on renvoi true car on est sur l'install d'une base
		 * donc inutile de tout cahrger la map
		 */
		public function __construct($id_base = null, $install_base = null) {
			$dbc = App::getDb();
			$temps_trajet = "";
			$map = [];

			if ($install_base != null) {
				return true;
			}
			
			if ($id_base == null) {
				$this->getParametres();
				
				$query = $dbc->select("_bataille_base.nom_base")
					->select("_bataille_base.points")
					->select("_bataille_base.posx")
					->select("_bataille_base.posy")
					->select("_bataille_base.ID_base")
					->select("identite.pseudo")
					->select("identite.ID_identite")
					->from("identite")
					->from("_bataille_base")
					->where("_bataille_base.ID_identite", "=", "identite.ID_identite", "", true)
					->get();
			}
			else {
				$query = $dbc->select("_bataille_base.nom_base")
					->select("_bataille_base.points")
					->select("_bataille_base.posx")
					->select("_bataille_base.posy")
					->select("_bataille_base.ID_base")
					->select("identite.ID_identite")
					->select("identite.pseudo")
					->from("identite")
					->from("_bataille_base")
					->where("_bataille_base.ID_base", "=", $id_base, "AND")
					->where("_bataille_base.ID_identite", "=", "identite.ID_identite", "", true)
					->get();
				
				$temps_trajet = DateHeure::Secondeenheure(Bataille::getDureeTrajet($id_base));
			}
			
			if ((is_array($query)) && (count($query) > 0)) {
				foreach ($query as $obj) {
					$ma_base = "";
					if ($obj->ID_base == Bataille::getIdBase()) {
						$ma_base = "ma-base";
					}
					
					$map[] = [
						"nom_base" => $obj->nom_base,
						"points" => $obj->points,
						"posx" => $obj->posx,
						"posy" => $obj->posy,
						"id_base" => $obj->ID_base,
						"id_identite" => $obj->ID_identite,
						"pseudo" => $obj->pseudo,
						"ma_base" => $ma_base,
						"temps_trajet" => $temps_trajet
					];
				}
				
				Bataille::setValues(["map" => $map]);
			}
		}
		//-------------------------- END BUILDER ----------------------------------------------------------------------------//



		//-------------------------- GETTER ----------------------------------------------------------------------------//
		/**
		 * @return integer
		 * fonction qui permet de récupérer le nombre de joueurs sur le serveur
		 */
		private function getNombreJoueur() {
			$dbc = App::getDb();
			
			$query = $dbc->select("nombre_joueur")->from("_bataille_nombre_joueur")->where("ID_nombre_joueur", "=", 1)->get();
			
			if ((is_array($query)) && (count($query) == 1)) {
				foreach ($query as $obj) return $obj->nombre_joueur;
			}
			
			return 0;
		}
		
		/**
		 * fonction qui sert à récupérer les parametres de la map
		 */
		private function getParametres() {
			$dbc = Bataille::getDb();

			$query = $dbc->select()->from("map")->where("ID_map", "=", 1)->get();

			foreach ($query as $obj) {
				Bataille::setValues([
					"largeur_map" => $obj->largeur,
					"hauteur_map" => $obj->hauteur
				]);

				$this->largeur = $obj->largeur;
				$this->hauteur = $obj->hauteur;
			}
		}

		/**
		 * @return array
		 * fonction utilisée lors de la création d'un compte
		 * renvoi les positions en x et y non occupées
		 */
		public function getPositionNewBase() {
			$this->getParametres();

			if ($this->getNombreJoueur() <= 150) {
				$posx = rand(0, 79);
				$posy = rand(0, 75);
			}
			else {
				$posx = rand(0, $this->largeur);
				$posy = rand(0, $this->hauteur);
			}

			//on test si il y a une base sur ces positions
			if (Bataille::getBaseExistPosition($posx, $posy)) {
				$this->getPositionNewBase();
			}
			else {
				//on a une position de base inexistante donc on la return
				return ["posx" => $posx, "posy" => $posy];
			}
		}
		//-------------------------- END GETTER ----------------------------------------------------------------------------//



		//-------------------------- SETTER ----------------------------------------------------------------------------//
		//-------------------------- END SETTER ----------------------------------------------------------------------------//
	}