<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Services extends App_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->model('service_model');
		$this->load->model('module_model');
		$this->load->model('customer_model');
		$this->load->model('billing_model');
	}
	
	/**
	 * Customer overview of everything
	 */
	public function index()
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'services');
		if ($permission['view'])
		{
		
			$this->load->view('header_with_sidebar');
		
			// get the customer title info, name and company
			$data = $this->customer_model->title($this->account_number);
			$this->load->view('customer_in_sidebar', $data);
			
			$this->load->view('moduletabs');
			
			$this->load->model('ticket_model');
			$this->load->view('messagetabs');
			
			$this->load->view('buttonbar');
			
			$data['categories'] = $this->service_model->service_categories($this->account_number);
			$this->load->view('services/heading', $data);
						
			// output the list of services
			$data['services'] = $this->service_model->list_services($this->account_number);
			$this->load->view('services/index', $data);
						
			// the history listing tabs
			$this->load->view('historyframe_tabs');	
			
			// show html footer
			$this->load->view('html_footer');
		}
		else
		{
			$this->module_model->permission_error();
		}	
		
	}
	
	public function edit()
	{
		if ($pallow_modify) {
    	  include('./modules/customer/edit.php');
    	}  else permission_error();
	}
	
	public function create($showall = NULL)
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'services');
		if ($permission['create'])
		{
			// load the module header common to all module views
			$this->load->view('module_header');

			// show the services available to add to this customer
			$data['showall'] = $showall;
			$this->load->view('services/create', $data);	
						
			// the history listing tabs
			$this->load->view('historyframe_tabs');	
			
			// show html footer
			$this->load->view('html_footer');
		}
		else
		{
			$this->module_model->permission_error();
		}
	}


	public function add_service($serviceid)
	{
		// GET Variables
		//$this->id = $this->input->post('id');
		$serviceid = $this->input->post('serviceid');
		$usagemultiple = $this->input->post('usagemultiple');
		$options_table_name = $this->input->post('options_table_name');
		$fieldlist = $this->input->post('fieldlist');
		$billing_id = $this->input->post('billing_id');
		$create_billing = $this->input->post('create_billing');
		$detail1 = $this->input->post('detail1');

		// add the services to the user_services table and the options table
		$fieldlist = substr($fieldlist, 1); 

		// loop through post_vars associative/hash to get field values
		$array_fieldlist = explode(",",$fieldlist);

		foreach ($base->input as $mykey => $myvalue) {
			foreach ($array_fieldlist as $myfield) {
				// print "$mykey<br>";
				if ($myfield == $mykey) {
					$fieldvalues .= ',\'' . $myvalue . '\'';
				}
			}
		}

		$fieldvalues = substr($fieldvalues, 1);

		// make the creation date YYYY-MM-DD HOUR:MIN:SEC
		$mydate = date("Y-m-d H:i:s");

		// if there is a create_billing request, create a billing record first
		if ($create_billing) {
			$billing_id = create_billing_record($create_billing, $account_number, $DB);
		}

		$user_service_id = create_service($account_number, $serviceid, $billing_id,
				$usagemultiple, $options_table_name,
				$fieldlist, $fieldvalues);


		// insert any linked_services into the user_services table
		$query = "SELECT * FROM linked_services WHERE linkfrom = $serviceid";
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		while ($myresult = $result->FetchRow()) {
			$linkto = $myresult['linkto'];

			create_service($account_number, $linkto, $billing_id,
					$usagemultiple, NULL, NULL, NULL);
		}	

		// add an entry to the customer_history to the activate_notify user
		service_message('added', $account_number, $serviceid,
				$user_service_id, NULL, NULL);

		// add a log entry that this service was added
		log_activity($DB,$user,$account_number,'create','service',$user_service_id,'success');

		print "$l_addedservice<p>";
		print "<script language=\"JavaScript\">window.location.href = ".
			"\"index.php?load=services&type=module\";</script>";
	}


	/*
	 * ------------------------------------------------------------------------
	 *  first step when adding a new service is to add the options/attributes
	 *  serviceid
	 *  detail1
	 * ------------------------------------------------------------------------
	 */
	public function add_options($serviceid, $detail1 = NULL)
	{
		// load the module header common to all module views
		$this->load->view('module_header');

		// load the add service options view
		$data['serviceid'] = $serviceid;
		$data['detail1'] = $detail1;
		$this->load->view('services/add_options_form', $data);

		// the history listing tabs
		$this->load->view('historyframe_tabs');	

		// show html footer
		$this->load->view('html_footer');
	}

	public function delete()
	{
		if ($pallow_remove) {
			include('./modules/customer/delete.php');
		} else permission_error();
	}

	public function fieldassets()
	{
		if ($pallow_remove) {
			include('./modules/customer/fieldassets');
		} else permission_error();        
	}

	public function history()
	{
		if ($pallow_remove) {
			include('./modules/customer/history');
		} else permission_error();
	}

	public function vendor()
	{
		if ($pallow_remove) {
			include('./modules/customer/vendor');
		} else permission_error();
	}

}

/* End of file customer */
/* Location: ./application/controllers/customer.php */
