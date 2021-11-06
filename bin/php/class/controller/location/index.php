<?php
namespace controller;
class Loc extends \controller\index {
	//SQL
	protected $Table = 'Loc';
	protected $Primary_Key = 'ID';
	//Columns
	protected $Name = null;
	protected $Street = null;
	protected $City = null;
	protected $State = null;
	protected $Zip = null;
	protected $Route = null;
	protected $Customer = null;
	protected $Division = null;
	protected $Balance = null;
	protected $Maintained = null;
	protected $Status = null;

	public function filter_Name( $Name = null ){
		if( isset( $Name ) && !in_array( $Name, array( '', ' ', null ) ) ){
			$parameters[] = $Name;
			$conditions[] = "Loc.Tag LIKE '%' + ? + '%'";
		}
	}

	public function filter_Street( $Street = null ){
		if( isset( $Street ) && !in_array( $Street, array( '', ' ', null ) ) ){
			$parameters[] = $Street;
			$conditions[] = "Loc.Address LIKE '%' + ? + '%'";
		}
	}

	public function filter_City( $City = null ){
		if( isset( $City ) && !in_array( $City, array( '', ' ', null ) ) ){
			$parameters[] = $City;
			$conditions[] = "Loc.City LIKE '%' + ? + '%'";
		}
	}

	public function filter_State( $State = null ){
		if( isset( $State ) && !in_array( $State, array( '', ' ', null ) ) ){
			$parameters[] = $State;
			$conditions[] = "Loc.State LIKE '%' + ? + '%'";
		}
	}

	public function filter_Zip( $Zip = null ){
		if( isset( $Zip ) && !in_array( $Zip, array( '', ' ', null ) ) ){
			$parameters[] = $Zip;
			$conditions[] = "Loc.Zip LIKE '%' + ? + '%'";
		}
	}

	public function filter_Route( $Route = null ){
		if( isset( $Route ) && !in_array( $Route, array( '', ' ', null ) ) ){
			$parameters[] = $Route;
			$conditions[] = "Loc.Route LIKE '%' + ? + '%'";
		}
	}

	public function filter_Customer( $Customer = null ){
		if( isset( $Customer ) && !in_array( $Customer, array( '', ' ', null ) ) ){
			$parameters[] = $Customer;
			$conditions[] = "Loc.Owner LIKE '%' + ? + '%'";
		}
	}

	public function filter_Division( $Division = null ){
		if( isset( $Division ) && !in_array( $Division, array( '', ' ', null ) ) ){
			$parameters[] = $Division;
			$conditions[] = "Loc.Zone LIKE '%' + ? + '%'";
		}
	}

	public function filter_Balance( $Balance = null ){
		if( isset( $Balance ) && !in_array( $Balance, array( '', ' ', null ) ) ){
			$parameters[] = $Balance;
			$conditions[] = "Loc.Balance LIKE '%' + ? + '%'";
		}
	}

	public function filter_Maintained( $Maintained = null ){
		if( isset( $Maintained ) && !in_array( $Maintained, array( '', ' ', null ) ) ){
			$parameters[] = $Maintained;
			$conditions[] = "Loc.Maint LIKE '%' + ? + '%'";
		}
	}

	public function filter_Status( $Status = null ){
		if( isset( $Status ) && !in_array( $Status, array( '', ' ', null ) ) ){
			$parameters[] = $Status;
			$conditions[] = "Loc.Status LIKE '%' + ? + '%'";
		}
	}
}
//Loc.Loc.ID,Loc.Tag.Name,Loc.Address.Street,Loc.City.City,Loc.State.State,Loc.Zip.Zip,Loc.Route.Route,Loc.Owner.Customer,Loc.Zone.Division,Loc.Balance.Balance,Loc.Maint.Maintained,Loc.Status.Status
?>