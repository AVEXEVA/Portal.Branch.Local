<?php
// MySQL Class
class Database {
	
	// Base variables
    public $lastError;         // Holds the last error
	public $lastQuery;         // Holds the last query
	public $result;            // Holds the MySQL query result
	public $records;           // Holds the total number of records returned
	public $affected;          // Holds the total number of records affected
	public $rawResults;        // Holds raw 'arrayed' results
	public $arrayedResult;     // Holds an array of the result

	private $hostname = "localhost";
	private $username = "nouvea12_admin";
	private $password = "*ofdavid605urself"; 
	private $database = "nouvea12_nouveau";
	
	/* *******************
	 * Class Constructor *
	 * *******************/
	
	function __construct(){
		$this->Connect();
		if(!$this->UseDB()){
			$this->lastError = 'Could not connect to database: ' . mysql_error($this->databaseLink);
			return false;
		}}

	/* *******************
	 * Private Functions *
	 * *******************/
	
	// Connects class to database
	// $persistant (boolean) - Use persistant connection?
	private function Connect($persistant = false){
		$this->CloseConnection();
		if($persistant) { $this->databaseLink = mysql_pconnect($this->hostname, $this->username, $this->password);}
		else { $this->databaseLink = mysql_connect($this->hostname, $this->username, $this->password); }
		if(is_null($this->databaseLink)){echo "couldn't connect";}
		if(!$this->databaseLink){
   			$this->lastError = 'Could not connect to server: ' . mysql_error($this->databaseLink);
			return false; }
		if(!$this->UseDB()){
			$this->lastError = 'Could not connect to database: ' . mysql_error($this->databaseLink);
			return false; }
		return true; }
		
	// Select database to use
	private function UseDB(){
		if(!mysql_select_db($this->database, $this->databaseLink)){
			$this->lastError = 'Cannot select database: ' . mysql_error($this->databaseLink);
			return false; }
		else { return true; }}
		
    /* ******************
     * Public Functions *
     * ******************/

    // Executes MySQL query
    public function query($query){
        if($this->result = mysql_query($query,$this->databaseLink)){
            if (gettype($this->result) === 'resource') {
                $this->records  = @mysql_num_rows($this->result);
                $this->affected = @mysql_affected_rows($this->databaseLink);} 
			else {
               $this->records  = 0;
               $this->affected = 0;}
            if($this->records > 0){
                $this->arrayResults();
                return $this->arrayedResult;} 
			elseif($this->result){return true;}}
		return false;}
    // 'Arrays' a single result
    public function arrayResult(){
        $this->arrayedResult = mysql_fetch_assoc($this->result) or die (mysql_error($this->databaseLink));
        return $this->arrayedResult; }

    // 'Arrays' multiple result
    public function arrayResults(){
    	if($this->records == 1){ return $this->arrayResult(); }
        $this->arrayedResult = array();
        while ($data = mysql_fetch_assoc($this->result)){ $this->arrayedResult[] = $data; }
        return $this->arrayedResult; }

    // 'Arrays' multiple results with a key
    public function arrayResultsWithKey($key='id'){
        if(isset($this->arrayedResult)){ unset($this->arrayedResult); }
        $this->arrayedResult = array();
        while($row = mysql_fetch_assoc($this->result)){
        	foreach($row as $theKey => $theValue){ 
        		$this->arrayedResult[$row[$key]][$theKey] = $theValue; }}
        return $this->arrayedResult; }

    // Returns last insert ID
    public function lastInsertID(){ return mysql_insert_id(); }

    // Return number of rows
    public function countRows($from, $where=''){
        $result = $this->select($from, $where, '', '', false, 'AND','count(*)');
        return $result["count(*)"]; }

    // Closes the connections
    public function closeConnection(){if($this->databaseLink){ mysql_close($this->databaseLink); }}

    //ESCAPE 
    public function escapeString($string){return mysql_real_escape_String($string,$this->databaseLink);}
}?>
