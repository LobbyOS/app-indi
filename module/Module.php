<?php
namespace Lobby\Module;

use Hooks;
use Lobby\Apps;
use Response;
use Lobby\Router;

class app_indi extends \Lobby\Module {

  public function init(){
    $appID = $this->app->getData("appID");
    
    $this->appAdminSetup();
    
    if($appID === null)
      return null;
    
    $App = new Apps($appID);
    $App->run();
    
    Hooks::addAction("router.finish", function(){
      /**
       * Route App Pages (/app/{appname}/{page}) to according apps
       */
      Router::route("/?[*:page]?", function($request){
        $appID = Apps::getInfo("id");
        $page = $request->page === null ? "/" : "/{$request->page}";
        
        if(substr($page, 0, 6) == "/admin"){
          return false;
        }else{
          $App = new \Lobby\Apps($appID);
          
          $class = $App->getInstance();
    
          /**
           * Set the title
           */
          Response::setTitle($App->info["name"]);
          
          $pageResponse = $class->page($page);
          if($pageResponse === "auto"){
            if($page === "/"){
              $page = "/index";
            }
            $html = $class->inc("/src/page{$page}.php");
            if($html){
              Response::setPage($html);
            }else{
              ser();
            }
          }else{
            if($pageResponse === null){
              ser();
            }else{
              Response::setPage($pageResponse);
            }
          }
        }
      });
    });
    
    Router::route("/app/[:appID]?/[**:page]?", function($request){
      if($request->appID === "admin")
        Response::redirect("admin/app/admin/$page" . $request->page);
      ser();
    });
    
    /**
     * Disable FilePicker Module
     */
    if(\Lobby\Modules::exists("filepicker")){
      \Lobby\Modules::disableModule("filepicker");
    }
    Router::route("/includes/lib/modules?/[**:page]?", function($request){
      ser();
    });
    
    \Lobby\UI\Panel::addTopItem('indiModule', array(
      "text" => "<img src='". $this->app->srcURL ."/src/image/logo.svg' />",
      "href" => "/admin/app/indi",
      "position" => "left",
      "subItems" => array(
        "appAdmin" => array(
          "text" => "App Admin",
          "href" => "/admin/app/$appID",
        ),
        "configIndi" => array(
          "text" => "Configure Indi",
          "href" => "/admin/app/indi"
        )
      )
    ));
  }
  
  public function appAdminSetup(){
    Router::route("/admin/app/[:appID]?/[**:page]?", function($request){
      $appID = $request->appID;
      $page = $request->page === null ? "/" : "/{$request->page}";
      
      $App = new \Lobby\Apps($appID);
      if(!$App->exists){
        ser();
        return null;
      }
      
      Hooks::addFilter("admin.view.sidebar", function($links) use ($appID, $App){
        $links["/admin/app/$appID"] = $App->info["name"];
        return $links;
      });
      
      Hooks::addFilter("app.manifest", function($info){
        $info["adminURL"] = L_URL . "/admin/app/" . $info["id"];
        return $info;
      });
      
      $class = $App->getInstance();
      /**
       * Set the title
       */
      Response::setTitle($App->info["name"]);
      
      $pageResponse = $class->page($page);
      if($pageResponse === "auto"){
        if($page === "/"){
          $page = "/index";
        }
        $html = $class->inc("/src/page/admin{$page}.php");
        if($html){
          Response::setPage($html);
        }else{
          ser();
        }
      }else{
        if($pageResponse === null){
          ob_start();
            echo ser("Error", "The app '<strong>{$AppID}</strong>' does not have an Admin Page. <a clear href='". \Lobby::u("/app/$AppID") ."' class='btn green'>Go To App</a>");
          $error = ob_get_contents();
          ob_end_clean();
          Response::setPage($error);
        }else{
          Response::setPage($pageResponse);
        }
      }
    });
  }
  
}
