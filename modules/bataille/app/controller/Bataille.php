<?php
	namespace modules\bataille\app\controller;
	use core\App;
	use core\database\Database;

	class Bataille {
		private static $ressource;
		private static $base;
		private static $batiment;
		private static $points;
		private static $unite;
		private static $centre_recherche;
		private static $missions_aleatoire;
		private static $nourriture;
		private static $map;
		private static $database;
		private static $nation;

		private static $id_base;

		public static $values = [];

		
		//-------------------------- BUILDER ----------------------------------------------------------------------------//
		public function __construct() {

		}
		//-------------------------- END BUILDER ----------------------------------------------------------------------------//
		
		
		
		//-------------------------- GETTER ----------------------------------------------------------------------------//
		/**
		 * @return array
		 * get array of all values wich will be used in the page
		 */
		public static function getValues() {
			return ["bataille" => self::$values];
		}

		//initilisation of all classes of battle
		//initialisation of Ressource class
		public static function getRessource() {
			if (self::$ressource == null) {
				self::$ressource = new Ressource();
			}

			return self::$ressource;
		}

		//initialisation of Base class
		public static function getBase() {
			if (self::$base == null) {
				self::$base = new Base();
			}

			return self::$base;
		}

		//initialisation of Batiment class
		public static function getBatiment() {
			if (self::$batiment == null) {
				self::$batiment = new Batiment();
			}

			return self::$batiment;
		}

		//initialisation of Points class
		public static function getPoints($points = null) {
			if (self::$points == null) {
				self::$points = new Points($points);
			}

			return self::$points;
		}

		//initialisation of Map class
		public static function getMap() {
			if (self::$map == null) {
				self::$map = new Map();
			}

			return self::$map;
		}

		//initialisation of Batiment class
		public static function getUnite() {
			if (self::$unite == null) {
				self::$unite = new Unite();
			}

			return self::$unite;
		}

		//initialisation of CentreRecherche class
		public static function getCentreRecherche() {
			if (self::$centre_recherche == null) {
				self::$centre_recherche = new CentreRecherche();
			}

			return self::$centre_recherche;
		}
		
		//initialisation of MissionsAleatoire class
		public static function getMissionsAleatoire() {
			if (self::$missions_aleatoire == null) {
				self::$missions_aleatoire = new MissionsAleatoire();
			}
			
			return self::$missions_aleatoire;
		}
		
		//initialisation of Nourriture class
		public static function getNourriture() {
			if (self::$nourriture == null) {
				self::$nourriture = new Nourriture();
			}
			
			return self::$nourriture;
		}

		//initialisation of Database Core connexion
		public static function getDb() {
			require_once("config.config.php");
			
			if (self::$database == null) {
				self::$database = new Database($type_co, $dbname, $dbuser, $dbpass, $dbhost);
			}
			return self::$database;
		}

		/**
		 * @return mixe
		 * récupère l'ID_identité du joueur
		 */
		public static function getIdIdentite() {
			return $_SESSION['idlogin'.CLEF_SITE];
		}

		/**
		 * @return mixed
		 * renvoi l'id_base du joueur
		 */
		public static function getIdBase() {
			if (self::$id_base == null) {
				self::$id_base = $_SESSION['id_base'];

				return self::$id_base;
			}

			return self::$id_base;
		}

		/**
		 * @return mixed
		 * renvoi le premier ID_base du joueur (première base et base princ du joueur)
		 */
		public static function getFirstBase() {
			$dbc = App::getDb();

			$query = $dbc->select("ID_base")->from("_bataille_base")
				->where("ID_identite", "=", self::getIdIdentite())
				->orderBy("ID_base")
				->limit(0, 1)
				->get();

			if ((is_array($query)) && (count($query) == 1)) {
				foreach ($query as $obj) return $obj->ID_base;
			}
		}

		/**
		 * @param $id_base
		 * @return array
		 * fonction qui renvoi les posisitons en x et y d'une base
		 */
		private static function getPosistionBase($id_base) {
			$dbc = App::getDb();
			
			$posx = 0;
			$posy = 0;

			$query = $dbc->select("posx")
				->select("posy")
				->from("_bataille_base")
				->where("ID_base", "=", $id_base)
				->get();

			foreach ($query as $obj) {
				$posx = $obj->posx;
				$posy = $obj->posy;
			}

			return ["posx" => $posx, "posy" => $posy];
		}

		/**
		 * @return int
		 * return now timestamp
		 */
		public static function getToday() {
			$today = new \DateTime();
			return $today->getTimestamp();
		}

		/**
		 * @param null $id_base -> sert si definit a recuperer l'id identite de la abse en question
		 * @return string|null
		 * recupere la date de la derniere connexion
		 */
		public static function getLastConnexion($id_base = null) {
			$dbc = App::getDb();

			if ($id_base === null) {
				$query = $dbc->select()->from("_bataille_infos_player")->where("ID_identite", "=", self::getIdIdentite())->get();
			}
			else {
				$query = $dbc->select("_bataille_infos_player.last_connexion")->from("_bataille_base")
					->from("_bataille_infos_player")
					->from("identite")
					->where("_bataille_base.ID_base", "=", $id_base, "AND")
					->where("_bataille_base.ID_identite", "=", "identite.ID_identite", "AND", true)
					->where("identite.ID_identite", "=", "_bataille_infos_player.ID_identite", "", true)
					->get();
			}

			if ((is_array($query)) && (count($query) > 0)) {
				foreach ($query as $obj) {
					return $obj->last_connexion;
				}
			}
		}

		/**
		 * @param string $nom_ressource
		 * @param $ressource
		 * @return array
		 * fonction qui permet de renvyer la couleur rouge si pas assez de ressource pour construire le batiment
		 * ou pour creer une unité...
		 */
		public static function getTestAssezRessourceBase($nom_ressource, $ressource) {
			$f = "get".ucfirst($nom_ressource);

			if ($ressource > Bataille::getRessource()->$f()) {
				return [
					"ressource" => $ressource,
					"class" => "rouge"
				];
			}

			return [
				"ressource" => $ressource,
				"class" => ""
			];
		}
		
		/**
		 * @param $id_base
		 * @param integer $vitesse = vitesse de l'unité en question
		 * @return number
		 * fonction qui renvoi le temps de trajet entre la base du joueur et une autre base en secondes
		 */
		public static function getDureeTrajet($id_base, $vitesse = 1) {
			//récupération de la posisiotn de la base du joueur + la base sur laquelle on a cliqué
			$base_joueur = self::getPosistionBase($_SESSION['id_base']);
			$base_autre = self::getPosistionBase($id_base);
			
			//calcul des distances séparant les deux bases en x et y
			//cette dstance sera multipliée par 15 sur x et y puis ajoutée pour avoir le temps du trajte en seconde
			$calc_x = abs($base_joueur['posx']-$base_autre['posx']);
			$calc_y = abs($base_joueur['posy']-$base_autre['posy']);
			
			$temps_voyage = (($calc_x*70)+($calc_y*70))/$vitesse;
			
			return $temps_voyage;
		}

		/**
		 * @param null $id_identite
		 * get nation of a player
		 */
		public static function getNation($id_identite = null) {
			$dbc = App::getDb();

			if (($id_identite === null) && (self::$nation == null)) {
				$id_identite = Bataille::getIdIdentite();
			}

			$query = $dbc->select("nation")
				->from("identite")
				->from("_bataille_nation")
				->where("identite.ID_identite", "=", $id_identite, "AND")
				->where("identite.ID_identite", "=", "_bataille_nation.ID_identite", "", true)
				->get();

			if ((is_array($query)) && (count($query) > 0)) {
				foreach ($query as $obj) {
					self::setValues(["nation" => $obj->nation]);
				}
			}
		}

		/**
		 * @param $posx
		 * @param $posy
		 * @return int
		 * fonction qui renvoi un ID_base en fonction de sa posx et posy et 0 si base inexistante
		 */
		public static function getBaseExistPosition($posx, $posy) {
			$dbc = App::getDb();

			$query = $dbc->select("ID_base")->from("_bataille_base")
				->where("posx", "=", $posx, "AND")
				->where("posy", "=", $posy)
				->get();

			if ((is_array($query)) && (count($query) == 1)) {
				foreach ($query as $obj) return $obj->ID_base;
			}

			return 0;
		}

		/**
		 * @return integer
		 * fonction qui permet de récupérer le nombre de joueurs sur le serveur
		 */
		public static function getNombreJoueur() {
			$dbc = App::getDb();

			$query = $dbc->select("nombre_joueur")->from("_bataille_nombre_joueur")->where("ID_nombre_joueur", "=", 1)->get();

			if ((is_array($query)) && (count($query) == 1)) {
				foreach ($query as $obj) return $obj->nombre_joueur;
			}

			return 0;
		}

		/**
		 * @param string $param
		 * @return mixed
		 * fonction qui sert à récupérer un parametre spécifique pour un batiment
		 * par exemple la vitesse d'un marchand ou  le nombred'emplacment de la base
		 */
		public static function getParam($param) {
			$dbc = self::getDb();

			$query = $dbc->select($param)->from("configuration")->where("ID_configuration", "=", 1)->get();

			if ((is_array($query)) && (count($query) == 1)) {
				foreach ($query as $obj) return $obj->$param;
			}
		}
		//-------------------------- END GETTER ----------------------------------------------------------------------------//
		
		
		
		//-------------------------- SETTER ----------------------------------------------------------------------------//
		/**
		 * set la date de derniere connexion a now
		 */
		public static function setLastConnexion($id_base = null) {
			$dbc = App::getDb();
			
			$id_identite = self::getIdIdentite();
			
			if ($id_base !== null) {
				$query = $dbc->select("ID_identite")->from("_bataille_base")->where("ID_base", "=", $id_base)->get();

				foreach ($query as $obj) $id_identite = $obj->ID_identite;
			}

			$dbc->update("last_connexion", date("Y-m-d H:i:s"))
				->from("_bataille_infos_player")
				->where("ID_identite", "=", $id_identite)
				->set();
		}

		/**
		 * @param $values
		 * can set values while keep older infos
		 */
		public static function setValues($values) {
			Bataille::$values = array_merge(Bataille::$values, $values);
		}
		//-------------------------- END SETTER ----------------------------------------------------------------------------//
	}