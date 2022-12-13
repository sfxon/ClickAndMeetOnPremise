{literal}<?php{/literal}

class {$DATA.data.admin_module}Master extends cModule {literal}{{/literal}
		var $template = 'admin_templates';
		var $navbar_title = '{$DATA.data.title}';
		var $navbar_id = 0;
		var $errors = array();
		var $errors_description = array();
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {literal}{{/literal}
				//If the user is not logged in..
				if(!isset($_SESSION['user_id'])) {literal}{{/literal}
						header('Location: index.php/account');
						die;
				{literal}}{/literal}
				
				//check the rights..
				if(false === cAccount::adminrightCheck('{$DATA.data.admin_module}', 'USE_MODULE', (int)$_SESSION['user_id'])) {literal}{{/literal}
						header('Location: index.php?s=cAdmin&error=304');
						die;
				{literal}}{/literal}
				
				//We use the Admin module for output.
				cAdmin::setSmallBodyExecutionalHooks();	
				
				//Now set our own hooks below the CMS hooks.
				$core = core();
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
		}
	
		///////////////////////////////////////////////////////////////////
		// processData
		///////////////////////////////////////////////////////////////////
		function process() {literal}{{/literal}
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb('Admin {$DATA.data.title}', 'index.php?s={$DATA.data.admin_module}');
				
				switch($this->action) {literal}{{/literal}
						case 'delete':
								$this->initData();
								$this->getContent();
								$this->delete();
								break;
						case 'confirm_delete':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s={$DATA.data.admin_module}&amp;action=delete&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb('L&ouml;schen best&auml;tigen', '');
								$this->navbar_title = 'L&ouml;schen best&auml;tigen';
								break;
						
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s={$DATA.data.admin_module}&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb('Bearbeiten', '');
								$this->navbar_title = 'Bearbeiten';
								break;
								
						case 'update':
								$this->initData();
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb('Bearbeiten', '');
								$this->navbar_title = 'Bearbeiten';
								break;
								
						case 'create':
								$this->create();
								$cAdmin->appendBreadcrumb('Neu', '');
								$this->navbar_title = 'Neu';
								break;
						
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s={$DATA.data.admin_module}&amp;action=create';
								$cAdmin->appendBreadcrumb('Neu', '');
								$this->navbar_title = 'Neu';
								break;
						default:
								$this->getList();
								break;
				{literal}}{/literal}
		{literal}}{/literal}
		
		///////////////////////////////////////////////////////////////////
		// "Delete" an entry..
		// We do not really delete any entry. We just flag it,
		// so it does not appear anymore.
		///////////////////////////////////////////////////////////////////
		private function delete() {literal}{{/literal}
				//check if user wants to delete or abort the action
				$button_do_not_delete = core()->getPostVar('button_do_not_delete');
				$button_delete = core()->getPostVar('button_delete');
				
				//abort button..
				if($button_do_not_delete !== NULL && $button_do_not_delete === 'not_delete') {literal}{{/literal}
						header('Location: index.php?s={$DATA.data.admin_module}&error=331');
						die;
				{literal}}{/literal}
				
				//delete button
				if($button_delete !== NULL && $button_delete === 'delete') {literal}{{/literal}
						{$DATA.data.data_module}::deleteById($this->data['data']['id']);
						
						header('Location: index.php?s={$DATA.data.admin_module}&success=332');
						die;
				{literal}}{/literal}
				
				//unknown operation (we didn't get proper input).
				header('Location: index.php?s=cAdmin&error=333');
				die;
		{literal}}{/literal}
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {literal}{{/literal}
    		{foreach from=$DATA.data.fields item=field}{strip}
       	{/strip}
        $this->data['data']['{$field.field_name}'] = {if $field.data_type == 'int' || $field.data_type == 'float' || $field.data_type == 'foreign_table_id'}0{else}''{/if};
     		{/strip}{/foreach}
		{literal}}{/literal}
		
		///////////////////////////////////////////////////////////////////
		// getList
		///////////////////////////////////////////////////////////////////
		function getList() {literal}{{/literal}
				$this->data['list'] = $this->loadList();
		{literal}}{/literal}
		
		///////////////////////////////////////////////////////////////////
		// Suche
		///////////////////////////////////////////////////////////////////
		function search() {literal}{{/literal}
				die( 'search' );
				die;
		{literal}}{/literal}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page content.
		///////////////////////////////////////////////////////////////////
		public function content() {literal}{{/literal}
				switch($this->action) {literal}{{/literal}
						case 'confirm_delete':
								$this->drawConfirmDeleteDialog();
								break;
						case 'new':
								$this->drawEditor();
								break;
						case 'edit':
								$this->drawEditor();
								break;
						default:
								$this->drawList();
								break;
				{literal}}{/literal}
		{literal}}{/literal}
		
		///////////////////////////////////////////////////////////////////
		// Draw the delete dialog.
		///////////////////////////////////////////////////////////////////
		public function drawConfirmDeleteDialog() {literal}{{/literal}
        $renderer = core()->getInstance('cRenderer');
        $original_template_path = $renderer->getTemplatePath();
				$renderer->setTemplatePath($this->getTemplatePath());
        $renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SITE_URLS', cSite::loadSiteUrls());
				$renderer->assign('NAVBARS', cAdminnavbaredit::loadNavbarList());
				$renderer->assign('ERRORS', $this->errors);
				$renderer->render('confirm_delete_dialog.html');
        
        $renderer->setTemplatePath($original_template_path);
		{literal}}{/literal}
		
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		public function drawEditor() {literal}{{/literal}
        $renderer = core()->getInstance('cRenderer');
        $original_template_path = $renderer->getTemplatePath();
				$renderer->setTemplatePath($this->getTemplatePath());
        $renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('editor.html');
        
        $renderer->setTemplatePath($original_template_path);
		{literal}}{/literal}
    
    ///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		public function getTemplatePath() {literal}{{/literal}
    		return 'modules/{$DATA.data.data_module}/';
    {literal}}{/literal}

		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {literal}{{/literal}
				$renderer = core()->getInstance('cRenderer');
        $original_template_path = $renderer->getTemplatePath();
        $renderer->setTemplatePath($this->getTemplatePath());
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('list.html');
        
        $renderer->setTemplatePath($original_template_path);
		{literal}}{/literal}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {literal}{{/literal}
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {literal}{{/literal}
						header('Location: index.php?s=admin{$DATA.data.admin_module}&error=305');
						die;
				{literal}}{/literal}
        
        $data['id'] = $id;

        {foreach from=$DATA.data.fields item=field}{strip}
       	{/strip}{if $field.field_name != 'id'}
        $data['{$field.field_name}'] = {if $field.data_type == 'int' || $field.data_type == 'float' || $field.data_type == 'foreign_table_id'}(int){/if}core()->getPostVar('{$field.field_name}');
     		{/strip}{/if}{/foreach}

				
				if(NULL === {$DATA.data.data_module}::loadById((int)$data['id'])) {literal}{{/literal}
						header('Location: index.php?s={$DATA.data.admin_module}&error=306');
						die;
				{literal}}{/literal}
				
				$id = $this->save($data);
				
				if(empty($id)) {literal}{{/literal}
						header('Location: index.php?s={$DATA.data.admin_module}&error=307');
						die;
				{literal}}{/literal}
	
				header('Location: index.php?s={$DATA.data.admin_module}&action=edit&id=' . $id . '&success=308');
				die;
		{literal}}{/literal}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {literal}{{/literal}
				$data['id'] = 0;
				
        {foreach from=$DATA.data.fields item=field}{strip}
       	{/strip}{if $field.field_name != 'id'}
        $data['{$field.field_name}'] = {if $field.data_type == 'int' || $field.data_type == 'float' || $field.data_type == 'foreign_table_id'}(int){/if}core()->getPostVar('{$field.field_name}');
     		{/strip}{/if}{/foreach}
				
				$id = $this->save($data);
				
				if(empty($id)) {literal}{{/literal}
						header('Location: index.php?s={$DATA.data.admin_module}&error=309');
						die;
				{literal}}{/literal}
	
				header('Location: index.php?s={$DATA.data.admin_module}&action=edit&id=' . $id . '&success=310');
				die;
		{literal}}{/literal}

		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {literal}{{/literal}
				return {$DATA.data.data_module}::loadList();
		{literal}}{/literal}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {literal}{{/literal}
				$id = (int)$data['id'];
		
				if(0 === $id) {literal}{{/literal}
						return {$DATA.data.data_module}::createInDB($data);
				{literal}}{/literal}
				
				{$DATA.data.data_module}::updateInDB($id, $data);
				
				return $data['id'];
		{literal}}{/literal}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {literal}{{/literal}
				$id = (int)core()->getGetVar('id');
				$this->data['data'] = {$DATA.data.data_module}::loadById($id);
		{literal}}{/literal}
    
    //////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system in the additional hooks..
		//////////////////////////////////////////////////////////////////////////////////
    public static function setAdditionalHooks() {literal}{{/literal}
				core()->setHook('cCore|init', 'addMenuBarEntries');
   {literal}}{/literal}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Callback function, adds a menu item.
		//////////////////////////////////////////////////////////////////////////////////
		public static function addMenuBarEntries() {literal}{{/literal}
				$cAdmin = core()->getInstance('cAdmin');
				
				if(false !== $cAdmin) {literal}{{/literal}
						$admin_menu_entry_path = array(
								array(
										'position' => 800,
										'title' => 'Module'
								),
								array(
										'position' => {$DATA.data.id},
										'title' => '{$DATA.data.title}'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s={$DATA.data.admin_module}');
				{literal}}{/literal}
		{literal}}{/literal}
    
{literal}}{/literal}
{literal}?>{/literal}