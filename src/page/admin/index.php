<div class="contents">
  <h1>Indi</h1>
  <p>Make a standalone Lobby app.</p>
  <h2>Config</h2>
  <?php
  $appID = Request::postParam("appID");
  if($appID !== null && CSRF::check()){
    if($appID === "")
      $this->data->remove("appID");
    else
      $this->data->saveValue("appID", $appID);
    echo sss("Saved", "Settings has been saved.");
  }
  $appID = $this->data->getValue("appID");
  ?>
  <form action="<?php echo Lobby::u("/admin/app/indi");?>" method="POST">
    <label>
      <span>App ID</span>
      <select name="appID">
        <option value="">Choose App:</option>
        <?php
        foreach(Lobby\Apps::getEnabledApps() as $app){
          echo "<option value='$app' ". ($appID === $app ? "selected='selected'" : "") .">$app</option>";
        }
        ?>
      </select>
    </label>
    <?php
    echo CSRF::getInput();
    ?>
    <cl/>
    <button class="btn green">Save</button>
  </form>
</div>
