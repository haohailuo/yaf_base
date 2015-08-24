<?php

/**
 *      [CodeJm!] Author CodeJm[codejm@163.com].
 *
 *      后台基础Controller父类, 可以进行登录，权限验证
 *      $Id: Backend.php 2014-07-27 20:22:43 codejm $
 */

class Core_BackendCtl extends \Core_BaseCtl {

    public $menuArr = array();
    public $admin = null;

    // 初始化
    public function init() {
        parent::init();


        // ---------------- 判断登录 --------------------------------------
        $admin = '';
        $relogin = false;
        // 判断session
        $admin = Tools_help::getSession('admin');
        if(empty($admin)){
            // 判断cookie
            $admin = Tools_help::getCookie('admin');
            if(empty($admin)){
                $this->redirect('/backend/Login/index');
            } else {
                $relogin = true;
            }
        }

        // cookie重新验证
        if($admin && $relogin) {
            $adminModel = new AdminModel();
            $data = $adminModel->getAdminById($admin['id']);
            if(empty($data)  || $data['roleid'] != 1 || $data['password'] != $admin['password']) {
                $this->redirect('/backend/Login/index');
            }
            $adminModel->reMemberMe($data);
            $admin = $data;
        }

        // E
        $this->_view->assign("curr_admin", $admin);
        $this->admin = $admin;


        $rbac = new Rbac_Core();
        if($admin['rid']) {
            $checkTitle = strtolower($this->moduleName.'/'.$this->controllerName.'/'.$this->actionName);
            $pid = $rbac->check($admin['rid'], $checkTitle);
            if(empty($pid)) {
                exit('您没有权限访问该网页！<a href="javascript:window.history.back();">返回</a> ');
            }
        }
        $menu = $rbac->getMenu($admin['rid'], false);

        // 后台菜单数组 S
        $menustr = new \Core_CBackendMenu($menu, $this->controllerName, $this->actionName);
        $this->_view->assign('backendMenu', $menustr);
        // E
    }
}

?>
