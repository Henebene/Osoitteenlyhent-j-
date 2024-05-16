<?php

/**
 * Luo halutun mittaisen satunnaismerkkijonon.
 *
 * @param int $length Muodostettavan merkkijonon pituus.
 * @return string Palauttaa muodostetun satunnaismerkkijonon.
 */

function generateHash($length) {

  // Määritellään lyhytosoitteessa käytettävät merkit.
  $chars = "2346789ABCDEFGHJKLMNOPQRSTUVWXYZ";

  // Lasketaan käytettävien merkkien lukumäärä.
  $charcount = strlen($chars);

  // Esitellään muuttuja, johon tulosmerkkijono koostetaan.
  $result = "";

  // Toista muodostettavan merkkijonon pituuden mukainen määrä.
  for ($i = 1; $i <= $length; $i++) {

    // Valitaan satunnaisesti merkin järjestysnumero. Jos
    // käytettäviä merkkejä on 24, niin index saa arvon
    // välillä 1-24.
    $index = rand(1, $charcount);

    // Haetaan järjestysnumeroa vastaava merkki käyttävien
    // merkkien merkkijonosta. Merkkijonoa voidaan käsitellä
    // kuten taulukkoa, ensimmäinen merkki on indeksipaikassa 0.
    $char = $chars[$index - 1];

    // Liitetään merkki tulosmuuttujan loppuun.
    $result = $result . $char;

  }

  // Palautetaan muodostettu tulosmerkkijono funktion paluuarvona.
  return $result;

}

?>