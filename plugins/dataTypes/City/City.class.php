<?php

class DataType_City extends DataTypePlugin {

	protected $dataTypeName = "City";
	protected $dataTypeFieldGroup = "geo";
	protected $dataTypeFieldGroupOrder = 20;
	protected $processOrder = 3;
	private $citiesByRegion;
	private $numRegions;


	public function __construct($runtimeContext) {
		// call the parent constructor (populates $L)
		parent::__construct();

		// if we're in the process of generating data, populate the private vars with the first and last names
		// needed for data generation
		if ($runtimeContext == "generation") {
			self::initCityList();
		}
	}


	public function generate($row, $placeholderStr, $existingRowData) {

		// see if this row has a region [N.B. This is something that could be calculated ONCE on the first row]
		$rowRegionInfo = array();
		$rowCountryInfo = array();
		/*
		while (list($key, $info) = each($existing_row_data)) {
			if ($info["data_type_folder"] == "StateProvince") {
				$row_region_info = $info;
				break;
			}
		}
		reset($existing_row_data);

		// see if this row has a country [N.B. This is something that could be calculated ONCE on the first row]
		if (empty($row_region_info)) {
			$row_country_info = array();
			while (list($key, $info) = each($existing_row_data)) {
				if ($info["data_type_folder"] == "Country") {
					$row_country_info = $info;
					break;
				}
			}
		}
		*/

		$randomCity = "";
/*		if (!empty($rowRegionInfo)) {
			$regionSlug = $rowRegionInfo["randomData"]["region_slug"];
			$randomCity = $this->citiesByRegion[$regionSlug][rand(0, count($this->citiesByRegion[$regionSlug])-1)]["city"];
		} else if (!empty($rowCountryInfo)) {
			// get all region IDs associated with this country
			$regions = Country_get_regions($row_country_info["randomData"]["id"]);
			$random_region_id = $regions[rand(0, count($regions)-1)]["region_id"];
			$random_city = $City_list[$random_region_id][rand(0, count($City_list[$random_region_id])-1)]["city"];
		} else {
		*/
			$randRegionSlug = array_rand($this->citiesByRegion);
			$randomCity = $this->citiesByRegion[$randRegionSlug][rand(0, count($this->citiesByRegion[$regionSlug])-1)]["city"];
		//}

		return $randomCity;
	}


	/**
	 * Called when the plugin is initialized during data generation.
	 */
	private function initCityList() {
		$prefix = Core::getDbTablePrefix();
		$response = Core::$db->query("
			SELECT *
			FROM {$prefix}cities
		");

		if ($response["success"]) {
			$cities = array();
			$citiesByRegion = array();
			while ($cityInfo = mysql_fetch_assoc($response["results"])) {
				if (!array_key_exists($cityInfo["region_slug"], $citiesByRegion)) {
					$citiesByRegion[$cityInfo["region_slug"]] = array();
				}

				$citiesByRegion[$cityInfo["region_slug"]][] = array(
					"city"    => $cityInfo["city"],
					"city_id" => $cityInfo["city_id"]
				);
			}
		}

		$this->citiesByRegion = $citiesByRegion;
		$this->numRegions = count($citiesByRegion);
	}


	public function getExportTypeInfo($exportType, $options) {
		$info = "";
		switch ($export_type) {
			case "sql":
				if ($options == "MySQL" || $options == "SQLite")
					$info = "varchar(100) default NULL";
				else if ($options == "Oracle")
					$info = "varchar2(100) default NULL";
				break;
		}

		return $info;
	}
}
