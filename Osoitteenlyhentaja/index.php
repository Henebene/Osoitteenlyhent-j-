
<?php
require_once 'utils.php';
require_once "classes.php";
 // Palvelun osoite
 $baseurl = "https://neutroni.hayo.fi/~hblom/be/redirect-custom/";

// Alustetaan pagestatus-muuttuja:
  //  0 = etusivu
  // Alustetaan pagestatus-muuttuja: 0 = etusivu
$pagestatus = 0;

// Määritellään tietokantayhteyden muodostamisessa
// tarvittavat tiedot.
$yhteysinfo = new yhteysinfo();
?>
<?php
if (isset($_POST["random"]))
{ 

   // Nappia on painettu, noudetaan URL-osoite lomakkeelta.
   $url = $_POST["url"];
   $lyhytosoite = parse_url($url);
   $host = $lyhytosoite['host'];
   $host = preg_replace('/^(www\.)|(\.com)$/', '', $host);
 
   try 
   {
    // Avataan tietokantayhteys luomalla PDO-oliosta ilmentymä.
    $pdo = $yhteysinfo->getPdo();
    $pdo->beginTransaction();    
     
    // Alustetaan lyhytosoitteen tarkistuskysely.
    $stmt = $pdo->prepare("SELECT 1
    FROM osoite
    WHERE tunniste = ?");
     
    // Muuttuja valitulle lyhytosoitteelle.
    $hash = "";

    // Toistetaan, kunnes sopiva lyhyosoite on 
    // löytynyt.
    while ($hash == "") 
    {
    
      // Muodostetaan lyhytosoite-ehdokas.
      $generated = $host . "_" . generateHash(5);
      
      // Tarkistetaan, ettei generoitu lyhytosoite
      // ole jo käytetty. Kysely ei tuota tulosta,
      // jos ehdokasta ei löydy taulusta.
      $stmt->execute([$generated]);
      $result = $stmt->fetchColumn();
      if (!$result) 
      {
        // Ehdokasta ei ole käytetty, valitaan
        // se käytettäväksi lyhytosoitteeksi.
        $hash = $generated;
      }
        
    }
        
        // Haetaan käyttäjän ip-osoite.
        $ip = $_SERVER['REMOTE_ADDR'];
          
        // Alustetaan lisäyslause.
        $stmt2 = $pdo->prepare("INSERT INTO osoite (tunniste, url, ip) VALUES (?, ?, ?)");
        $stmt3 = $pdo->prepare("INSERT INTO ohjaukset (tunniste) VALUES (?)");
        // Lisätään osoite tietokantaan.
        $stmt2->execute([$hash, $url, $ip]);
        $stmt3->execute([$hash]);
        $pdo->commit();
        
        // Osoite on lisätty tietokantaan, muodostetaan
        // käyttäjälle tietosivu.
        $pagestatus = 1;
        $shorturl = $baseurl . $hash;
      } 
      catch (PDOException $e) 
      {
        $pagestatus = -2;
        $error = $e->getMessage();
      }    
        
    if (isset($_GET["hash"]) == true)
    {
      try
      {
        $pdo = $yhteysinfo->getPdo();
        $stmt = $pdo->prepare("SELECT url
        FROM osoite
        WHERE tunniste = ?");
  
        $stmt->execute([$hash]);
        $rivi = $stmt->fetch(); 
  
        if ($rivi) 
        {
          $url = $rivi['url'];
          header("Location: " . $url);
          exit;
        }
        else 
        {
          $pagestatus = -1;
        }
      } 
        catch (PDOException $e) 
        {
          $pagestatus = -2;
          $error = $e->getMessage();
        }   
    }
}
?>
<?php
if (isset($_POST["custom"]))
{
    $url = $_POST["url"];
    $custom = $_POST["kustomi"];
    $lyhytosoite = parse_url($url);
    $host = $lyhytosoite['host'];
    $host = preg_replace('/^(www\.)|(\.com)$/', '', $host);

    try 
    {
      $pdo = $yhteysinfo->getPdo();
      $pdo->beginTransaction();    
      
      $stmt = $pdo->prepare("SELECT url
      FROM osoite
      WHERE tunniste = ?");
      
      $generated = $host . "_" . $custom;
      $ip = $_SERVER['REMOTE_ADDR'];
      $stmt->execute([$generated]);
      $result = $stmt->fetchColumn();
      if (!$result == $generated) 
      {
        $hash = $generated;
        $stmt2 = $pdo->prepare("INSERT INTO osoite (tunniste, url, ip) VALUES  (?, ?, ?)");  
        $stmt3 = $pdo->prepare("INSERT INTO ohjaukset (tunniste) VALUES (?)");
        $stmt2->execute([$hash, $url, $ip]);
        $stmt3->execute([$hash]);
        $pdo->commit();
        $pagestatus = 1;
        $shorturl = $baseurl . $hash;                   
      }
      else
      {
          $pagestatus = -1;
      }
    }
    catch(PDOException $e) 
    {
      $pagestatus = -2;
      $error = $e->getMessage();
    }
}
              
if (isset($_GET["hash"]) == true)
{
  try 
  {
    $pdo = $yhteysinfo->getPdo();
    $hash = $_GET["hash"];

    $pdo->beginTransaction();
    $stmtkliks = $pdo->prepare("UPDATE ohjaukset SET käyttökerrat = käyttökerrat + 1 WHERE tunniste = ?");
    $stmthash = $pdo->prepare("SELECT url FROM osoite WHERE tunniste = ?");

    $stmtkliks->execute([$hash]);
    $stmthash->execute([$hash]);
    $rivi = $stmthash->fetch();
    
    $pdo->commit();

    if ($rivi) 
    {
      $url = $rivi['url'];
      header("Location: " . $url);
      exit;
    }
    else 
    {
      $pagestatus = -1;
    }
  } 
  catch (PDOException $e) 
  {
    $pagestatus = -2;
    $error = $e->getMessage();
  }   
}

if (isset($_POST["kliks"]))
{
  try
  {
    $url = $_POST["url"];
    $vikaslash = strrpos($url, '/');
    $hash = substr($url, $vikaslash,);
    $hash = ltrim($hash, '/');

    $pdo = $yhteysinfo->getPdo();

    $stmt = $pdo->prepare("SELECT käyttökerrat FROM ohjaukset WHERE tunniste = ?");
    $stmt->execute([$hash]);
    $kliks = $stmt->fetchColumn();
    $pagestatus = 2;
  }
  catch (PDOException $e) 
  {
    $pagestatus = -2;
    $error = $e->getMessage();
  }   
}
      
?>
<!DOCTYPE html>
<html>
  <head>
  <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <title>Lyhentäjä</title>
    <meta charset='UTF-8'>
    <meta name="viewport" 
          content="width=device-width, initial-scale=1.0">
  </head>
  <body>
    <div class='page'>
      <header>
        <h1>Lyhentäjä</h1>
        <div>ällistyttävä osoitelyhentäjä</div>
      </header>
      <main>
      <?php
  if ($pagestatus == 0) {
?>
        <div class='form'>
          <p>Tällä palvelulla voit lyhentää pitkän osoitteen
             lyhyeksi. Syötä alla olevaan kenttään pitkä osoite 
             ja paina random nappia jos haluat sattumasti luodun lyhytosoitteen tai custom nappia niin saat luoda käyttöösi oman lyhytosoitteen, 
             jota voit jakaa eteenpäin.</p>
          <form action='' method='POST'>
            <label for='url'>Syötä lyhennettävä osoite tai syötä sinun lyhennettyosoite ylempään kenttään ja katso kuinka suosittu se on .</label>
            <div class='url'>
                    <input type='text' name='url' 
                    placeholder='tosi pitkä osoite'>
            <label for='url'>Syötä custom osoite</label>
                    <input type='text' name='kustomi' 
                    placeholder='custom osoite'>
                    <div class="buttons">
                      <input type='submit' class='random' name='random' value='random'>
                      <input type='submit' class='custom' name='custom' value='custom'>
                      <input type='submit' class='kliks' name='kliks' value='Kuinka suosittu lyhytosoitteesi on?'>
                    </div>
            </div>
          </form>
        </div>
<?php
  }
  if ($pagestatus == -1) {
    ?>
            <div class='error'>
              <h2>HUPSISTA!</h2>
              <p>Näyttää siltä, että lyhytosoite on jo käytössä. 
                 Ole hyvä ja kokeile toista lyhytosoitetta.</p>
              <p>Voit tehdä <a href="<?=$baseurl?>">tällä 
                 palvelulla</a> oman lyhytosoitteen.</p>
            </div>
    <?php
  }
  
  if ($pagestatus == -3) {
    ?>
            <div class='error'>
              <h2>HUPSISTA!</h2>
              <p>Näyttää siltä, että lyhytosoitetta ei löytynyt. 
                 Ole hyvä ja tarkista antamasi osoite.</p>
              <p>Voit tehdä <a href="<?=$baseurl?>">tällä 
                 palvelulla</a> oman lyhytosoitteen.</p>
            </div>
    <?php
      }
    
      if ($pagestatus == -2) {
        ?>
                <div class='error'>
                  <h2>NYT KÄVI HASSUSTI!</h2>
                  <p>Nostamme käden ylös virheen merkiksi,  
                     palvelimellamme on pientä hässäkkää.
                     Ole hyvä ja kokeile myöhemmin uudelleen.</p>
                  <p>(virheilmoitus: <?=$error?>)</p>
                </div>
        <?php
          }
          if ($pagestatus == 1) {
            ?>
                    <div class='finish'>
                      <h2>JIPPII!</h2>
                      <p>Loit itsellesi uuden lyhytosoitteen, 
                         aivan mahtava juttu! Jatkossa voit käyttää 
                         seuraavaa osoitetta:
                         <div class='code'><?=$shorturl?></div></p>
                      <p>Voit tehdä uuden lyhytosoitteen 
                         <a href="<?=$baseurl?>">täällä</a>.</p>
                    </div>
            <?php
              }
              if ($pagestatus == 2) {
                ?>
                        <div class='finish'>
                          <h2>HUHHUH</h2>
                          <p>Sinun lyhytosoitetta on klikattu näin monta kertaa
                             <div class='code2'><?=$kliks?></div></p>
                          <p>Voit tehdä uuden lyhytosoitteen 
                             <a href="<?=$baseurl?>">täällä</a>.</p>
                        </div>
                <?php
                  }
?>
      </main>
      <footer>
        <hr>
        &copy; Kurpitsa Solutions
      </footer>
    </div>
  </body>  
</html>