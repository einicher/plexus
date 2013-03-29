<?php
	function localeDate($str)
	{
		$str = str_replace('Monday', 'Montag', $str);
		$str = str_replace('Tuesday', 'Dienstag', $str);
		$str = str_replace('Wednesday', 'Mittwoch', $str);
		$str = str_replace('Thursday', 'Donnerstag', $str);
		$str = str_replace('Friday', 'Freitag', $str);
		$str = str_replace('Saturday', 'Samstag', $str);
		$str = str_replace('Sunday', 'Sonntag', $str);

		$str = str_replace('January', 'Januar', $str);
		$str = str_replace('February', 'Februar', $str);
		$str = str_replace('March', 'März', $str);
		$str = str_replace('May', 'Mai', $str);
		$str = str_replace('June', 'Juni', $str);
		$str = str_replace('July', 'Juli', $str);
		$str = str_replace('October', 'Oktober', $str);
		$str = str_replace('December', 'Dezember', $str);

		return $str;
	}

	$this->set('Error 404', 'Fehler 404');
	$this->set('The requested page was not found.', 'Die aufgerufene Seite wurde nicht gefunden.');
	$this->set('Do you want to {{}} it?', 'Willst du sie {{}}?');
	$this->set('create', 'erstellen');
	$this->set('Create', 'Erstellen');
	$this->set('new', 'neu');
	$this->set('New', 'Neu');
	$this->set('edit', 'bearbeiten');
	$this->set('Edit', 'Bearbeiten');
	$this->set('copy', 'kopieren');
	$this->set('Copy', 'Kopieren');
	$this->set('translate', 'uebersetzen');
	$this->set('Translate', 'Übersetzen');
	$this->set('cancel', 'abbrechen');
	$this->set('Cancel', 'Abbrechen');
	$this->set('login', 'anmelden');
	$this->set('Login', 'Anmelden');
	$this->set('Logout', 'Abmelden');
	$this->set('logout', 'abmelden');
	$this->set('Search', 'Suche');
	$this->set('search', 'Suche');
	$this->set('Your search for “{{}}” matched {{}} results.', 'Deine Suche nach „{{}}“ ergab {{}} Treffer.');
	$this->set('Search for “{{}}”', 'Suche nach „{{}}“');
	$this->set('Find', 'Finden');
	$this->set('Name, ID, Email', 'Name, ID, E-Mail');
	$this->set('Password', 'Passwort');
	$this->set('Email', 'E-Mail');
	$this->set('Published', 'Veröffentlicht');
	$this->set('Published {{}}', 'Veröffentlicht {{}}');
	$this->set('May be in the future.', 'Kann auch in der Zukunft liegen.');
	$this->set('Published hidden', 'Versteckt veröffentlicht');
	$this->set('Draft', 'Entwurf');
	$this->set('Y-m-d', 'd.m.Y');
	$this->set('Y-m-d H:i', 'd.m.Y H:i');
	$this->set('yy-mm-dd', 'dd.mm.yy');
	$this->set('Remember me on this computer', 'Angemeldet bleiben');
	$this->set('+ Add New Widget', '+ Element hinzufügen');
	$this->set('Preferences', 'Einstellungen');
	$this->set('General Settings', 'Allgemein');
	$this->set('Components', 'Komponenten');
	$this->set('User', 'Benutzer');
	$this->set('Page', 'Seite');
	$this->set('Image', 'Bild');
	$this->set('Database', 'Datenbank');
	$this->set('Gallery', 'Galerie');
	$this->set('File', 'Datei');
	$this->set('Choose a widget', 'Wähle ein Element aus');
	$this->set('Simple Text', 'Einfacher Text');
	$this->set('today', 'heute');
	$this->set('today at', 'heute um');
	$this->set('yesterday', 'gestern');
	$this->set('yesterday at', 'gestern um');
	$this->set('the day after tomorrow', 'übermorgen');
	$this->set('the day after tomorrow at', 'übermorgen um');
	$this->set('tomorrow', 'morgen');
	$this->set('tomorrow at', 'morgen um');
	$this->set('two days ago', 'vorgestern');
	$this->set('two days ago at', 'vorgestern um');
	$this->set('l, F j, Y', 'l, j. F Y');
	$this->set('m/d/Y', 'd.m.Y');
	$this->set('on {{}}', 'am {{}}');
	$this->set('on {{}} at {{}}', 'am {{}} um {{}}');
	$this->set('Monday', 'Montag');
	$this->set('Tuesday', 'Dienstag');
	$this->set('Wednesday', 'Mittwoch');
	$this->set('Thursday', 'Donnerstag');
	$this->set('Friday', 'Freitag');
	$this->set('Saturday', 'Samstag');
	$this->set('Sunday', 'Sonntag');
	$this->set('January', 'Januar');
	$this->set('February', 'Februar');
	$this->set('March', 'März');
	$this->set('April', 'April');
	$this->set('May', 'Mai');
	$this->set('June', 'Juni');
	$this->set('July', 'Juli');
	$this->set('October', 'Oktober');
	$this->set('November', 'fut');
	$this->set('December', 'Dezember');
	$this->set('Users', 'Benutzer');
	$this->set('Groups', 'Gruppen');
	$this->set('Source', 'Quelle');
	$this->set('Read more', 'Weiterlesen');
	$this->set('« Newer', '« Neuere');
	$this->set('Older »', 'Ältere »');
	$this->set('-- Filter by Type --', '-- Nach Typ filtern --');
	$this->set('Filtered by type {{}}', 'Gefiltert durch Typ {{}}');
	$this->set('Lost my password', 'Passwort vergessen');
	$this->set('Tag “{{}}”', 'Tag „{{}}“');
	$this->set('Are you a real human being?', 'Bist du Mensch oder Maschine?');
	$this->set('zero', 'null');
	$this->set('one', 'eins');
	$this->set('two', 'zwei');
	$this->set('three', 'drei');
	$this->set('four', 'vier');
	$this->set('five', 'fünf');
	$this->set('six', 'sechs');
	$this->set('seven', 'sieben');
	$this->set('eight', 'acht');
	$this->set('nine', 'neun');
	$this->set('ten', 'neun');
	$this->set('eleven', 'elf');
	$this->set('twelfe', 'zwölf');
	$this->set('thirteen', 'dreizehn');
	$this->set('fourteen', 'vierzehn');
	$this->set('fifteen', 'fünfzehn');
	$this->set('sixteen', 'sechzehn');
	$this->set('seventeen', 'siebzehn');
	$this->set('eighteen', 'achtzehn');
	$this->set('nineteen', 'neunzehn');
	$this->set('Zero', 'Null');
	$this->set('One', 'Eins');
	$this->set('Two', 'Zwei');
	$this->set('Three', 'Drei');
	$this->set('Four', 'Vier');
	$this->set('Five', 'Fünf');
	$this->set('Six', 'Sechs');
	$this->set('Seven', 'Sieben');
	$this->set('Eight', 'Acht');
	$this->set('Nine', 'Neun');
	$this->set('Ten', 'Neun');
	$this->set('Eleven', 'Elf');
	$this->set('Twelfe', 'Zwölf');
	$this->set('Thirteen', 'Dreizehn');
	$this->set('Fourteen', 'Vierzehn');
	$this->set('Fifteen', 'Fünfzehn');
	$this->set('Sixteen', 'Sechzehn');
	$this->set('Seventeen', 'Siebzehn');
	$this->set('Eighteen', 'Achtzehn');
	$this->set('Nineteen', 'Neunzehn');
	$this->set('in words', 'in Worten');
	$this->set('{{}} {{}} {{}} is ', '{{}} {{}} {{}} ist ');
	$this->set('The following required fields were left empty: {{}}', 'Folgende benötigte Felder wurden nicht ausgefüllt: {{}}');
	$this->set('PAGE', 'Seite');
	$this->set('PAGEs', 'Seiten');
	$this->set('POST', 'Artikel');
	$this->set('POSTs', 'Artikel');
	$this->set('COMMENT', 'Kommentar');
	$this->set('COMMENTs', 'Kommentare');
	$this->set('THREAD', 'Thread');
	$this->set('THREADs', 'Threads');
	$this->set('TUTORIAL', 'Tutorial');
	$this->set('TUTORIALs', 'Tutorials');
	$this->set('GROUP', 'Gruppe');
	$this->set('GROUPs', 'Gruppen');
	$this->set('IMAGE', 'Bild');
	$this->set('IMAGEs', 'Bilder');
	$this->set('FILE', 'Datei');
	$this->set('FILEs', 'Dateien');
	$this->set('user', 'Benutzer');
	$this->set('The result of your captcha calculation is wrong.', 'Das Ergebnis deiner Rechnung war falsch.');
	$this->set('A user with the email “{{}}” already exists. {{}}', 'Ein Benutzer mit der E-Mail „{{}}“ existiert bereits. {{}}');
	$this->set('You can request an new password here.', 'Du kannst hier ein neues Passwort anfordern.');
	$this->set('Success', 'Erfolg');
	$this->set('A temporary password was sent to you via email.', 'Ein vorübergehendes Passwort wurde dir per E-Mail zugeschickt.');
	$this->set('Access rights needed', 'Zugriffsrechte benötigt');
	$this->set('You do not have the necessary permissions to add new contents.', 'Du hast nicht die nötigen Rechte um neue Inhalte hinzuzufügen.');
	$this->set('Suggestions', 'Vorschläge');
	$this->set('Choose data type', 'Datentyp auswählen');
	$this->set('Dashboard', 'Dashboard');
	$this->set('Connections', 'Verbindungen');
	$this->set('Requests', 'Anfragen');
	$this->set('Home', 'Startseite');
	$this->set('Send a connection request to another Plexus website.', 'Eine andere Plexus Seite mit dieser koppeln:');
	$this->set('Pending connection requests', 'Offene Anfragen');
	$this->set('Currently there are no pending connection requests.', 'Derzeit gibt es keine offenen Anfragen.');
	$this->set('Send request', 'Anfrage senden');
	$this->set('Active connections', 'Gekoppelte Plexus-Seiten');
	$this->set('Disconnect', 'Entkoppeln');
	$this->set('Accept', 'Annehmen');
	$this->set('Refuse', 'Ablehnen');
	$this->set('Mark all read', 'Alle als gelesen markieren');
	$this->set('All', 'Alle');
	$this->set('Unread', 'Ungelesen');
	$this->set('Refresh', 'Aktualisieren');
	$this->set('Own', 'Eigene');
	$this->set('Foreign', 'Fremde');
	$this->set('Blocked IP addresses', 'Blockierte IP-Adressen');
	$this->set('Block IP', 'IP blockieren');
	$this->set('Blocked IPs', 'Blockierte IPs');
	$this->set('Currently there are no blocked IPs.', 'Zurzeit gibt es keine blockierten IPs.');
	$this->set('New password', 'Neues Passwort');
	$this->set('Confirm new password', 'Neues Passwort bestätigen');
	$this->set('Lost password', 'Passwort vergessen');
	$this->set('Please enter your username or email address. You will receive a temporary password via email.', 'Bitte gib deinen Profil-Namen oder deine E-Mail-Adresse an. Du erhältst dann ein vorübergehendes Passwort per E-Mail.');
	$this->set('Name or Email', 'Name oder E-Mail');
	$this->set('Request new password', 'Neues Passwort anfordern');
	$this->set('Your temporary password for {{}}', 'Dein vorübergehendes Passwort für {{}}');
	$this->set('lost-password', 'Passwort-vergessen');
	$this->set('Hello {{}}!

Here is a temporary password for your account on {{}}:

Login: {{}}

Name: {{}}

Email: {{}}

Temporary password: {{}}


You receive this mail, because you or someone else requested a temporary password to login on {{}}.
If this wasn\'t you, just ignore this mail, the created password will expire within a week.',

'Hallo {{}}!

Hier ist ein vorübergehendes Passwort für dein Profil auf {{}}:

Login: {{}}

Name: {{}}

E-Mail: {{}}

Vorübergehendes Passwort: {{}}


Du erhältst dieses E-Mail, weil du oder jemand anders ein vorübergehendes Passwort auf {{}} angefordert hat.
Wenn das nicht du warst ignoriere dieses E-Mail einfach, das Passwort läuft in einer Woche automatisch ab.');
	$this->set('Trackbacks for {{}} are possible.', 'Trackbacks für {{}} sind möglich.');
	$this->set('To get a trackback link type in the string “{{}}” in reverse order:', 'Um einen Trackback-Link zu erhalten, tippe die Zeichenkette „{{}}“ in umgekehrter Reihenfolge ein:');
	$this->set('Send', 'Abschicken');
	$this->set('Just send a Trackback to the following URL: {{}}', 'Sende einfach einen Trackback an folgende URL: {{}}');
	$this->set('Currently there are no trackbacks.', 'Derzeit gibt es keine Trackbacks.');
	$this->set('In cache parts of a webpage are temporarily saved to increase the loading time and to reduce the number of accesses on the database.', 'Im Cache werden Teile einer Webseite zwischengespeichert um die Ladezeiten zu erhöhen und die Anzahl der Zugriffe auf die Datenbank zu reduzieren.');
	$this->set('If changes you made to the website are not visible to the public, you might need to clear the cache so the outdated data will be deleted from the cache.', 'Wenn Änderungen die du an der Webseite gemacht hast für die Öffentlichkeit nicht sichtbar sind, musst du vermutlich den Cache leeren um die veralteten Daten aus dem Cache zu entfernen.');
	$this->set('Clear cache', 'Cache leeren');
	$this->set('The cache was cleared.', 'Der Cache wurde geleert.');
	$this->set('Files that could not be deleted', 'Dateien die nicht gelöscht werden konnten');
	$this->set('Successfully deleted files', 'Erfolgreich gelöschte Dateien');
	$this->set('Languages', 'Sprachen');
	$this->set('Here you can set custom root paths like {{}} for your multiple languages.', 'Hier kannst du eigene Sprach-Abkürzungen wie {{}} einstellen, die den Pfaden in Links dann bei mehrsprachigen Inhalten vorangestellt werden.');
	$this->set('Language name', 'Name der Sprache');
	$this->set('+ Add Language', '+ Sprache hinzufügen');
	$this->set('Next Post', 'Nächster Beitrag');
	$this->set('Previous Post', 'Vorheriger Betrag');
	$this->set('', '');
	$this->set('', '');
	$this->set('', '');
	$this->set('', '');
?>
