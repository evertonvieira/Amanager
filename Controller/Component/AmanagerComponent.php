<?php
App::uses('Controller', 'Controller');
App::uses('AmanagerAppModel', 'Amanager.Model');

class AmanagerComponent extends Component {

    /**
     * components
     *
     * @var array
     */
    public $components = array( 'Session');

    /**
     * controller
     *
     * @var object
     */
    var $controller;

    /**
     *
     * Armazena a url responsável por exibir o formulário de login.
     *
     * login_action
     *
     * @var array
     */
    var $login_action = array(
        'controller'=>'users',
        'plugin' => 'amanager',
        'action'=>'login',
        'admin'=>false
    );

    /**
     *
     * Armazena a url para onde o usuário é redirecionado após o login.
     * A ideia é que a mesma só seja usada se previous_url estiver vazio.
     *
     * login_redirect
     *
     * @var array
    */
    var $login_redirect = array(
        'controller'=>'amanager',
        'plugin' => 'amanager',
        'action'=>'index',
        'admin'=>false
    );

    /**
     *
     * Armazena a url para onde o usuário é redirecionado após se deslogar do sistema.
     *
     * logout_redirect
     *
     * @var array
     */
    var $logout_redirect = array(
        'controller'=>'pages',
        'plugin' => false,
        'action'=>'display',
        'admin'=>false
    );

    /**
     *
     * Armazena a url para onde o usuário é redirecionado quando o mesmo não tem acesso ao endereço
     * acessado.
     *
     * access_denied
     *
     * @var array
     */
    var $access_denied = array(
        'controller'=>'users',
        'action'=>'access_denied',
        'plugin'=>'amanager',
        'admin'=>false
    );

    /**
     *
     * Armazena a url anterior a atual para ser usada em redirecionamentos
     *
     * url_prev
     *
     * @var array
     */
    var $url_prev = array();

    /**
     *
     * Parâmetros que são levados em consideração na hora da verificação de permissão
     *
     * @var array
     */
    var $parametros_levados_em_conta = array(
        'controller',
        'action',
        'plugin',
        'admin'
    );

    function __construct(ComponentCollection $collection, $settings = array()) {
        parent::__construct($collection, $settings);
    }

    /**
     * Método acionado pelo controlador do projeto
     *
     * @param object $controller
     * @param array $options
     * @return void
     */
    function beforeFilter(Controller $controller, $options = array() ) {
        // Verifica se o usuário tem permissão para a área
        if( !$this->isAllowed($controller->request->params) ){
            if(!$this->Session->read('Amanager.url_prev')){
                $this->Session->write('Amanager.url_prev', FULL_BASE_URL . $controller->here );
                $this->url_prev = FULL_BASE_URL . $controller->here;
            }
            if( $this->is_logged() ){
                $this->Session->setFlash(__('Acesso negado!'), 'msg/error');
                if(
                    $controller->request->params['action'] != $this->access_denied['action']
                    or $controller->request->params['controller'] != $this->access_denied['controller']
                    or $controller->request->params['plugin'] != $this->access_denied['plugin']
                ){
                    $this->controller->redirect( $this->access_denied );
                }
            }
            $this->controller->redirect( $this->login_action );
        }
    }

    function initialize(Controller $controller = null) {
        $this->controller = $controller;
    }

    function startup(Controller $controller = null) {
        if( isset($this->settings->login_action) ) $this->login_action = $this->settings['login_action'];
        if( isset($this->settings->login_redirect) ) $this->login_redirect = $this->settings['login_redirect'];
        if( isset($this->settings->logout_redirect) ) $this->logout_redirect = $this->settings['logout_redirect'];
    }

    public function login($data_login){
        $this->url_prev = $this->Session->read('Amanager.url_prev');
        $this->Session->write('Amanager', $data_login);
        $url_prev =  $this->url_prev;
        $this->Session->delete('Amanager.url_prev');
        $this->controller->redirect( $url_prev );
    }

    public function is_logged(){
        return $this->Session->read('Amanager.User')?true:false;
    }

    public function logout() {
        if(!$this->is_logged()){
            $this->Session->setFlash(__('Você tentou acessar um endereço não acessível neste momento'));
            $this->controller->redirect($this->logout_redirect);
        }
        $this->Session->delete('Amanager');
        $this->Session->setFlash(__('Você foi desconectado do sistema'));
        $this->controller->redirect($this->logout_redirect);
    }

    /**
     * Função que checa se o(s) grupo(s) do usuário logado
     * tem acesso a área solicitada
     * Transforma os parâmetros em uma url e checa se a existe uma regra
     *
     * @param  array  $params Parâmetros da url pretendida
     * @return boolean
     */
    function isAllowed($params = null) {
        $params = $this->limpaPrametros($params);
        /* Verifica se a url é livre, se sim já libera o acesso */
        if($this->checks_urls_free($params)){
            return true;
        }
        /* Se estiver no grupo administrators permite */
        $groups = $this->Session->read('Amanager.Group');
        $master = Configure::read('Amanager.group_master');
        $adm = Set::extract("{n}/.[name={$master}]", $groups);
        if($adm){
            return true ;
        }
        if(!$groups){
            return false;
        }
        $alow = false;
        foreach( $groups as $group ){
            $rules = Hash::sort($group['Rule'], '{n}.order', 'desc');
            foreach( $rules as $actions ){
                foreach( $actions['Action'] as $action ){
                    $action_alow =  $action['alow'];
                    $action =   $this->limpaPrametros(json_decode($action['alias'], true));
                    $diff = Hash::diff($action, $params);
                    if(count($diff) == 0 && $action_alow){
                        $alow = true;
                    }
                }

            }
        }
        $url = Router::url($params  + array("base" => false));
        CakeLog::write('info', ' - IcjeckNob8' . ' > ' . ($alow?'Permitida':'Não permitida') . ' a entrada para ' . $this->get_user_logged('username') . ' em ' . $url);
        return $alow;
    }

    /**
     * password_generator method
     *
     * Gera uma senha com a quantidade de caracteres passada no parâmetro $size
     * Se o parâmetro não for informado, a quantidade de 10 caracteres é assumida
     *
     * @params integer $size
     *
     */
    public function password_generator($size = 10) {
        return substr( str_shuffle( 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$' ) , 0 , $size );
    }

    /* :: API :: */

    /**
     *
     * Valida os dados enviados de outro modelo
     *
     * @param object $model
     * @param array $dados
     * @return boolean true or array $errors
      */
    public function valida_user( $dados ) {
        $return = array();
        if ( !isset( $dados['User'] ) ){
            $return['erro'][] = 'O índice User não foi encontrado no array de dados enviado. Por favor informe ao administrador do sistema este erro. [#ShekyuAds9';
            return $return;
        }
        App::import('Model', 'Amanager.User');
        $User = new User();
        $User->set($dados);
        if( ! $User->validates() ) {
            $erros = $User->invalidFields();
            foreach( $erros as $key => $var ){
                $return['erro'][$key] = $erros[$key][0];
            }
            return $return;
        }
    return true;

  }

    /**
     * Insere um usuário enviado de fora do Plugin
     *
     * @param array $user
     * @return integer with the user id or false
     */
    public function add_user( $user ) {
        if ( !isset( $user['User'] ) ){
          return false;;
        }
        App::import('Model', 'Amanager.User');
        $User = new User();
        $User->create();
        if ($User->save( $user )) {
            $user = $User->read();
            $user_id = $user['User']['id'];
            return $user_id;
        } else {
            return false;
        }
        return false;
    }

    /**
     * Obtém o id do grupo de acordo com o nome passado
     *
     * @param string $nome
     * @return integer $id
    */
    public function get_id_group( $nome ) {
        App::import('Model', 'Amanager.Group');
        $Group = new Group();
        $group = $Group->findByName($nome);
        return $group['Group']['id'];
    }

    /**
     * obtém os dados de usuário de acordo com o id de EntitysUser passado
     *
     * @param integer $entitys_user_id
     * @return array $data or false
     */
    public function get_user_data( $entitys_user_id ) {
        App::import('Model', 'Amanager.EntitysUser');
        $EntitysUser = new EntitysUser();
        $EntitysUser->id = $entitys_user_id;
        $entitys_user = $EntitysUser->read();
        return $entitys_user['User'] ;
    }

    /**
     * obtém os dados do usuário logado
     *
     * @param string $attribute
     * @return array $data or false
     */
    public function get_user_logged( $attribute = false){
        $_attribute = !$attribute?"":".{$attribute}" ;
        $user = $this->Session->read("Amanager.User{$_attribute}");
        return !$user?false:$user;
    }

    /**
     * Obtém os grupos do usuário logado
     *
     * @return array $groups
     */
    public function get_group_names_logged(){
        $groups = $this->Session->read('Amanager.Group');
        $g = array();
        foreach( $groups as $group ){
            $g[] = $group['name'];
        }
        return $g;
    }

    /**
    * Insere nova chave de ataulização de senha para o usuário especificado
    *
    * @param integer $user_id
    * @return string $passwordchangecode
    **/
    public function set_password_change_code($user_id) {
        App::import('Model', 'Amanager.User');
        $User = new User();
        $User->id = $user_id;
        $passwordchangecode = hash('sha512', mktime());
        $User->saveField('passwordchangecode', $passwordchangecode, array( 'validate'=>false, 'callbacks'=>false) );
        return $passwordchangecode ;
    }

    /**
     * Verifica se a url pretendida está entre as url livres
     *
     * @param array $params
     * @return boolean
    */
    public function checks_urls_free($params) {
        $return = false;
        /* Obtém as urls livres configuradas */
        $urls_livres = Configure::read('Amanager.urls_livres');
        /* Corre todas as url livres em busca da url pretendida */
        foreach($urls_livres as $url_livre){
            $url_livre = $this->limpaPrametros($url_livre);
            if( $url_livre == $params ){
                $return = true;
            }
        }
        return $return;
    }

    /**
     * Limpa os parâmetros usado para gerar urls
     * só mantendo os que serão usados para verificação de permissão
     *
     * @param  $url
     * @param  array $manter informa os índices que mesmo estando em parametros_levados_em_conta
     * @return $url
     */
    public function clear_url($url, $manter = array()) {
        // Se não for um array o transforma em um
        if(!is_array($url)){
           $url = Router::parse($url, false );
        }
        // Se só foi informado o controlador
        if(count($url) == 1){
            if(isset($url['controller'])){
                $url = Router::url($url);
            }
        }
        foreach( $url as $k  => $v ){
            if( !in_array($k, $this->parametros_levados_em_conta) && !in_array($k, $manter) ){
                unset($url[$k]);
            }
        }
        if (array_key_exists('key', $url)) {
            if( $url['plugin'] == NULL ){
                unset( $url['plugin'] );
            }
        }
        if (array_key_exists('plugin', $url)) {
            if(empty($url['plugin'])) $url['plugin'] = false;
        }
        if (array_key_exists('admin', $url)) {
            if(empty($url['admin']))unset( $url['admin'] );
        }
        // Se tiver admin no nome do controlador, remove
        $url['controller'] = str_replace( 'admin/', '',$url['controller']);
        return $url ;
    }

    /**
     * Normaliza parâmetros passados para o padrão do sistema (CakePHP)
     *
     * @param  array  $params
     * @return array
     */
    public function normalize($params =array()){
        //$url = Router::url($params  + array("base" => false));
        //$params = Router::parse($url);
        if(isset($params['named'])){
            unset($params['named']);
        }
        if(isset($params['pass'])){
            unset($params['pass']);
        }
        return $params;
    }

    protected function _compare(){
    }

    /**
     * Limpa os parâmetros
     * Remove os parâmetros que não estão em $this->parametros_levados_em_conta
     * @param  array $parametros Obtidos no controlador
     * @return array com apenas os parâmetros levados em conta para a checagem
     * da permissão.
     */
    public function limpaPrametros($parametros){
        $acesso = array();
        foreach ($parametros as $key => $parametro) {
            if ( in_array($key, $this->parametros_levados_em_conta)) {
                if($key == 'action'){
                    $key = Inflector::slug($key);
                }
                $acesso[$key] = $parametro;
            }
        }
        return $acesso;
    }

}

?>
