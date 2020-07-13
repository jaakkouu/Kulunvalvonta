<h1 align="center">
Kulunvalvonta
</h1>
<p align="center">
    <img src="https://github.com/jaakkouu/kulunvalvonta/blob/master/images/image1.jpg" width="250" />
    <img src="https://github.com/jaakkouu/kulunvalvonta/blob/master/images/image2.jpg" width="250" />
</p>

<p>Tavoitteena oli luoda pieni kulunvalvontalaite. Projekti toteutettiin ESP32 levyn päällä. Kulunvalvonta otettiin käyttöön siten, että prototyyppi kiinnitettiin teipillä seinään. Seurattavaan oveen liimattiin magneetti. Prototyypin reed-switch liimattiin oven karmiin kiinni magneetin korkeudelle. </p>

<p>Reed-switchin pinnien yhdistyessä ohjelma teki GET-kutsun palvelimelleni kotireitittimen kautta Telnetin avulla kohdistuen PHP-tiedostoon. Pinnit yhdistyivät, kun magneetti liikkui Reed-switchin lähettyville.</p>

<p align="center">
  <img src="https://github.com/jaakkouu/kulunvalvonta/blob/master/images/OpeningDoor.gif?raw=true">
</p>

<p>Kutsuun liitettiin tieto kotireittimeen liitetyistä WIFI-laitteista. Laitteet erotettiin toistaan MAC-osoitteilla ja kullekin laitteelle annettiin oma ID. MAC-osoitteet noudettiin telnetin yhteydessä status24g tiedostosta CAT:lla. Laitteita verrattiin PHP-tiedostossa MYSQL palvelimella tallennettuihin tietoihin. Käyttäjästä tallennettiin tietokantaan poistumis ja saapumisaika sekä niiden erotus eli poissaoloaika.</p>

<img src="https://github.com/jaakkouu/kulunvalvonta/blob/master/images/TelnetShellScript.png">

<img src="https://github.com/jaakkouu/kulunvalvonta/blob/master/images/Index.PNG">
