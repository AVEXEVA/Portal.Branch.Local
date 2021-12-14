<?php
namespace singleton;
class fontawesome extends \singleton\index {
	//Helpers
	public function __call( $function, $_args){
	    if( method_exists( $this, $function ) ){
	    	$this->$function($_args);
	    } else {
			$this->icon( strtolower( $function ), 1 );
		}
	}
	public function icon( $icon, $size ){		?><i class='fa fa-<?php echo $icon;?> fa-fw fa-<?php echo $size;?>x'></i><?php }
	public function blank($size=null){ 			self::icon( 'blank', $size ); }

	//Data columns
	public function Field($size=null){			self::icon( 'bolt', $size ); }
	public function Name( $size = 1 ){ 			self::icon( 'Name', $size ); }
	public function Type( $size = 1 ){ 			self::icon( 'Type', $size ); }
	public function Status( $size = 1 ){ 		self::icon( 'Status', $size ); }
	public function Description( $size = 1 ){ 	self::icon( 'paragraph', $size ); }
	public function Note( $size = 1 ){ 			self::icon( 'sticky-note-o', $size ); }
	public function Notes( $size = 1 ){         self::Note( $size ); }

	//DataTypes
	public function Currency( $size = 1 ){ 		self::icon( 'dollar', $size ); }
	public function Date( $size = 1 ){ 			self::icon( 'calendar', $size ); }
	public function Email($size=null){			self::icon( 'envelope', $size ); }
	public function Phone($size=null){			self::icon( 'phone', $size ); }
	public function Time( $size = 1 ){ 			self::icon( 'clock-o', $size ); }

	//Address columns
	public function Address($size=null){		self::icon( 'map-signs', $size ); }
	public function Street( $size = 1 ){ 		self::icon( 'info', $size ); }
	public function City( $size = 1 ){ 			self::icon( 'info', $size ); }
	public function State( $size = 1 ){ 		self::icon( 'info', $size ); }
	public function Zip( $size = 1 ){ 			self::icon( 'city', $size ); }

	//TS Table Names
	
	public function Contact( $size = 1 ){ 		self::icon( 'address-card', $size ); }
	public function Contacts( $size = 1 ){ 		self::Contact( $size ); }
	public function Contract( $size = 1 ){ 		self::icon( 'file-signature', $size ); }
	public function Collection( $size = 1 ){ 	self::icon( 'file-invoice-dollar', $size ); }
	public function Customer( $size = 1 ){ 		self::icon( 'industry', $size ); }
	public function Division( $size = 1 ){ 		self::icon( 'sitemap', $size ); }
	public function Employee( $size = 1 ){ 		self::icon( 'user', $size ); }
	public function Employees( $size = 1 ){ 	self::icon( 'users', $size ); }
	public function Invoice( $size = 1 ){ 		self::icon( 'stack-overflow', $size ); }
	public function Job( $size = 1 ){ 			self::icon( 'suitcase', $size ); }
	public function Lead( $size = 1 ){ 			self::icon( 'project-diagram', $size ); }
	public function Leads( $size = 1 ){ 		self::Lead( $size ); }
	public function Location( $size = 1 ){ 		self::icon( 'building', $size ); }
	public function Proposal( $size = 1 ){ 		self::icon( 'pencil', $size ); }
	public function Requisition( $size = 1 ){ 	self::icon( 'barcode', $size ); }
	public function Requisitions( $size = 1 ){ 	self::Requisition( $size ); }
	public function Route( $size = 1 ){ 		self::icon( 'route', $size ); }
	public function Supervisor( $size= 1 ){     self::icon( 'chalkboard-teacher', $size ); }
	public function Territory( $size = 1 ){ 	self::icon( 'black-tie', $size ); }
	public function Ticket( $size = 1 ){ 		self::icon( 'ticket', $size ); }
	public function Unit( $size = 1 ){ 			self::icon( 'cogs', $size ); }
	public function Violation( $size = 1 ){ 	self::icon( 'warning', $size ); }

	//Portal Table Names
	public function Connection( $size = 1 ){ 	self::icon( 'exchange', $size ); }
	public function Error( $size = 1 ){ 		self::icon( 'exclamation-triangle', $size ); }
	public function User( $size = 1 ){ 			self::icon( 'user-shield', $size ); }
	public function Users( $size = 1 ){ 		self::icon( 'user-shield', $size ); }

	//Extended Business Logic
	public function Attendance($size=null){		self::icon( 'calendar', $size ); }
	public function DOB($size=null){			self::icon( 'eye', $size ); }
	public function Maintenance($size=null){	self::icon( 'wrench', $size ); }
	public function Modernization($size=null){	self::icon( 'hammer', $size ); }
	public function Operations($size=null){		self::icon( 'cogs', $size ); }
	public function Repair($size=null){			self::icon( 'wrench', $size ); }
	public function Resident($size=null){		self::icon( 'flag', $size ); }
	public function Sales($size=null){			self::icon( 'black-tie', $size ); }
	public function Tasks($size=null){			self::icon( 'tasks', $size ); }
	public function Testing($size=null){		self::icon( 'cogs', $size ); }

	//Input Types
	public function Check($size=null){			self::icon( 'check', $size ); }
	public function Checkbox($size=null){		self::icon( 'check', $size ); }


	//Card Types
	public function Info( $size=null ){			self::icon( 'info', $size ); }
	public function Information( $size=null ){	self::Info( $size ); }

	//Page Links
	public function Archive($size=null){		self::icon( 'archive', $size ); }
	public function Dispatch($size=null){		self::icon( 'headphones', $size ); }
	public function Home($size=null){			self::icon( 'home', $size ); }
	public function Human_Resources($size=null){self::icon( 'child', $size ); }
	public function Map($size=null){			self::icon( 'map', $size ); }
	public function Payroll($size=null){		self::icon( 'money', $size ); }
	public function Profile($size=null){		self::icon( 'user-circle', $size ); }
	public function Sitemap($size=null){		self::icon( 'sitemap', $size ); }
	public function Safety_Report($size=null){	self::icon( 'exclamation', $size ); }
	
	//Smart Elements
	public function Chart($size=null){			self::icon( 'bar-chart-o', $size ); }
	public function Calendar($size=null){		self::icon( 'calendar', $size ); }
	public function Calendar_Plus($size=null){	self::icon( 'calendar-plus-o', $size ); }
	public function List($size=null){			self::icon( 'list', $size ); }
	public function Table($size=null){			self::icon( 'table', $size ); }

	//Navigation Buttons
	public function Back($size=null){			self::Previous( $size ); }
	public function Next($size=null){			self::icon( 'arrow-right', $size ); }
	public function Previous($size=null){		self::icon( 'arrow-left', $size ); }
	public function Refresh($size=null){		self::icon( 'refresh', $size ); }
	public function Search($size=null){			self::icon( 'search', $size ); }
	
	//Form Buttons
	public function Add($size=null){			self::icon( 'plus-circle', $size ); }
	public function Create($size=null){			self::icon( 'plus-circle', $size ); }
	public function Edit($size=null){			self::icon( 'edit', $size ); }
	public function Delete($size=null){			self::icon( 'trash', $size ); }
	public function Save($size=null){			self::icon( 'save', $size ); }
	public function CSV($size=null){			self::icon( 'file-csv', $size ); }
	public function Export($size=null){			self::CSV( $size ); }

	//Other Buttons
	public function Logout($size=null){			self::icon( 'sign-out', $size ); }
	public function Print($size=null){			self::icon( 'print', $size ); }
	public function Controls($size=null){		self::icon( 'user-secret', $size ); }

	//Other
	public function Activities($size=null){		self::icon( 'feed', $size ); }
	public function Admin($size=null){			self::icon( 'eye', $size ); }
	public function Birthday($size=null){		self::icon( 'birthday-cake', $size ); }
	public function Book($size=null){			self::icon( 'book', $size ); }
	public function Dashboard($size=null){		self::icon( 'dashboard', $size ); }
	public function Delivery($size=null){		self::icon( 'truck', $size ); }
	public function Financial($size=null){		self::icon( 'bar-chart-o', $size ); }
	public function History($size=null){		self::icon( 'history', $size ); }
	public function Legal($size=null){			self::icon( 'legal', $size ); }
	public function Request($size=null){		self::icon( 'info', $size ); }
	public function Pnl($size=null){			self::icon( 'bar-chart-o', $size ); }
	public function Review($size=null){			self::icon( 'book', $size ); }
	public function Service($size=null){		self::icon( 'phone', $size ); }
	public function Work($size=null){			self::icon( 'bolt', $size ); }
	public function Timesheet($size=null){		self::icon( 'clock-o', $size ); }
	public function Update($size=null){			self::icon( 'compass', $size ); }
	public function Website($size=null){		self::icon( 'external-link', $size ); }
	
	//ETC
	public function Clock($size=null){			self::icon( 'clock-o', $size ); }
	public function Hours($size=null){			self::icon( 'hourglass', $size ); }
	public function Web($size=null){			self::icon( 'bookmark', $size ); }
	public function Purchase($size=null){		self::icon( 'shopping-cart', $size ); }
	public function Privilege($size=null){		self::icon( 'codepen', $size ); }
	public function Report($size=null){			self::icon( 'paperclip', $size ); }
	public function Inspection($size=null){		self::icon( 'paperclip', $size ); }
	public function Github($size=null){			self::icon( 'github', $size ); }
	public function Paragraph($size=null){		self::icon( 'paragraph', $size ); }
}?>
