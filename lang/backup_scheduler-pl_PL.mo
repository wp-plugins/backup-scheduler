��    �            �	  I   �	  �   6
      �
  9   �
  v   !  E   �  9   �          ,  U   ;  d   �  �   �     �  )   �     �  %        5     D     R  "   i     �     �  H   �     �  !     #   6     Z     i      �  
   �  4   �     �  6   �     )  N   :  F   �     �  N   �  @   -     n       R   �  D   �  |   %     �  A   �  +   �       :   +     f  8   v     �     �  f   �     B  6   b  [   �     �  [     Y   p  (   �  +   �  *        J  9   Y     �  2   �  	   �  e   �  i   P     �  k   �     =  j   S  #   �  �   �  9   p  �   �  '   8  �   `  -   �     !     7  e   I     �  l   �     /  l   G  W   �       w   +     �  -   �     �       *   &  �   Q  <   �  /        @  	   `     j     �  �   �  	   7      A      [      k   C   z      �   H   �   #   !  a   0!  �   �!  %   I"  5   o"  s   �"  f   #  ^   �#  U   �#  D   5$  �   z$  W   %  �   n%  �   �%  =   �&  E   '  "   H'  <   k'      �'  u   �'  Y   ?(  �   �(  +   1)  r   ])  $   �)  q   �)  7   g*  y   �*  Q   +     k+  
   s+     ~+     �+     �+     �+  V   �+     ,     %,     5,     I,     \,     d,  !   �,  &   �,     �,  B   �,  S   &-  �   z-  3   
.  4   >.  \   s.  C   �.  ?   /     T/     f/  h   y/  S   �/  s   60  !   �0     �0     �0     �0     1     11  #   H1     l1     �1     �1  Q   �1  #   2     /2     L2  
   k2     v2  '   �2     �2  ^   �2     03  E   ?3     �3  A   �3  ?   �3     4  O   -4  5   }4     �4     �4  O   �4  9   ,5  p   f5     �5  7   �5  5   6     U6  )   f6     �6  6   �6     �6     �6  V   7      X7  8   y7  [   �7     8  _   +8  ?   �8     �8     �8  %   
9     09  ?   B9     �9  0   �9     �9  ^   �9  S   0:     �:  �   �:     ;  �   +;     �;  �   �;  9   w<  �   �<  $   >=  �   c=  3   >     @>     P>  b   a>     �>  |   �>     U?  i   k?  X   �?      .@  �   O@     �@  2   �@     "A     ;A  ;   ZA  �   �A  K   DB  &   �B     �B     �B     �B     �B  �   C  	   �C     �C     �C     �C  Q   �C     LD  [   QD  $   �D  f   �D  �   9E  6   F  <   CF  �   �F  U   	G  X   _G  T   �G  C   H  �   QH  {   �H  �   wI  �   J  E   K  _   JK      �K  /   �K  '   �K  t   #L  V   �L  �   �L  -   �M  {   �M  $   DN  y   iN  6   �N  �   O  Z   �O     P  
   P     #P     =P     XP     eP  b   zP     �P     �P     Q     Q     /Q     ;Q     YQ  %   xQ     �Q  >   �Q  This plugin enables scheduled backups of important parts of your website! Just configure the schedule and the content to be save and enjoy: a background process will be triggered by the person who visits your website! Customize the name of the files? The SQL extraction is in progress (%s entries extracted). The ZIP creation is in progress (%s files has been added in %s zip files and the current size of the zip files is %s). The FTP sending is in progress (%s files has been stored in the FTP). The MAIL sending is in progress (%s files has been sent). Cancel this process Please wait... (For now, there is no backup files... You should wait or force a backup (see below) ) Please wait, a backup is in progress for %s seconds! Wait until %s seconds for an automatic restart. This error message may also be generated if the chunk size is too big: try first to set the chunk size to 1Mo in order to avoid any memory saturation of your server and then increase it slowly... (SQL extraction - ending) Add this string to the name of the files: (SQL extraction) (SQL extraction - nothing to be done) (ZIP creation) (FTP sending) (FTP sending - ending) (FTP sending - nothing to be done) Backup of %s on %s (%s) (MAIL sending) Your Wordpress installation cannot send emails (with heavy attachments)! (MAIL sending - ending) The name of the files will be %s. (MAIL sending - nothing to be done) (END - ending) An unknown error occured! A new backup has been generated! Dear sirs, Here is attached the %s on %s backup files for today Best regards, Please find hereafter a summary of the backup process. Global synthesis The backup process has started on %s and have lasted %s minutes and %s seconds %s is the string of the present option. You may set this option to %s. SQL synthesis The SQL extraction has started on %s and have lasted %s minutes and %s seconds %s entries have been extracted and have been stored in %s files. %s created on %s ZIP synthesis The ZIP creation phase has started on %s and have lasted %s minutes and %s seconds %s files have been stored into %s split files (zip, z01, z02, etc.). Please note that %s files have been excluded from the backup process because their sizes exceed the chunk size (i.e. %s Mo). %s (size %s) These zip files are accessible for %s days at the following path: %s is a random string for security reasons. FTP synthesis The %s zip files have been stored on the specified FTP: %s %s stored on %s ERROR: %s has not been stored. The error message was: %s Backup of %s on %s No host has been defined Your PHP installation does not support SSL features... Thus, please use a standard FTP and not a FTPS! The host %s cannot be resolved! The folder %s cannot be created on the FTP repository! The file %s cannot be transfered to the FTP repository %s! The ftp_put function returns: %s %s is the extension (i.e. %s). The file %s cannot be transfered to the FTP repository and PASV mode cannot be entered : %s The specified folder %s does not exists. Please create it so that the transfer may start! The login/password does not seems valid! The folder %s does not seems to be writable It seems impossible to switch to PASV mode Everything OK! Save the SQL content of the sub-sites in different files: What do you want to save? All directories (the full Wordpress installation): (i.e. %s) Check this option if you want to save everything. Be careful, because the backup could be quite huge! Sorry, but you should install/activate %s on your website. Otherwise, this plugin will not work properly! The plugins directory: Check this option if you want to save all plugins that you have installed and that you use on this website. The themes directory: Check this option if you want to save all themes that you have installed and that you use on this website. The upload directory for this blog: Check this option if you want to save the images, the files, etc. that you have uploaded on your website to create your articles/posts/pages. All upload directories (for this site and the sub-blogs): Check this option if you want to save the images, the files, etc. that people have uploaded on their websites to create articles/posts/pages. The upload directory for the main site: Check this option if you want to save the images, the files, etc. that you have uploaded on your main website to create your articles/posts/pages. How often do you want to backup your website? The upload directory: The SQL database: Check this option if you want to save the text of your posts, your configurations, etc. for this blog All SQL databases: Check this option if you want to save all texts of posts, configurations, etc. for all blogs in this website Only your SQL database: Check this option if you want to save the text of your posts, your configurations, etc. for the main website Check this option if you want to save the text of your posts, your configurations, etc. The maximum file size (in MB): Please note that the zip file will be split into multiple files to comply with the maximum file size you have indicated Frequency (in days): Do you want that the backup is sent by email? Send the backup files by email: If so, please enter your email: Do you want to add a suffix to sent files: This option allows going round the blocking feature of some mail provider that block the mails with zip attachments (like GMail). You do not need to fill this field if no mail is to be sent. Do you want that the backup is stored on a FTP? Save the backup files on a FTP? FTP host: Should be at the form %s or %s Time of the backups: If %s is omitted then it is automatically added when connecting to your FTP. This is useful if you get an 404 error submitting these parameters with %s. FTP port: By default the port is %s Your FTP login: Your FTP pass: Click on that button %s to test if the above information is correct Test Create sub-folder with date in your FTP repository for all backup files: Add a prefix to the created folder: Your PHP installation does not support FTP features, thus this option has been disabled! Sorry... Please note that 0 means midnight, 1 means 1am, 13 means 1pm, etc. The backup will occur at that time (server time) so make sure that your website is not too overloaded at that time. Advanced - Memory and time management What is the maximum size of allocated memory (in MB): On some Wordpress installation, you may have memory issues. Thus, try to reduce this number if you face such error. For your information, the memory limit of your webserver is %s whereas the present memory usage is %s. It is recommended that the maximum attachment size is not set to a value higher than this one. Please note that the files greater than this limit won't be included in the zip file! What is the maximum time for the php scripts execution (in seconds): Even if you do not have time restriction, it is recommended to set this value to 15sec in order to avoid any killing of the php scripts by your web hoster. Here is the backup files. You can force a new backup or download previous backup files. Please note that the current GMT time of the server is %s. If it is not correct, please configure the Wordpress installation correctly. Please also note that the backup won't be end exactly at that time. The backup process could take up to 6h especially if you do not have a lot of traffic on your website and/or if the backup is quite huge. An automatic backup will be launched in %s days and %s hours. The backup process has started %s hours ago but has not finished yet. Force a new backup (with Mail/FTP) Force a new backup (without any external storage or sending) How to restore the backup files? To restore the backups, and if you have backuped the full installation, you will have to execute the following steps: Save all zip files (i.e. *.zip, *.z01, *.z02, etc.) in a single folder on your hard disk. Unzip these files by using IZArc, Winzip, or Winrar (others software could not support these multipart zip and consider that the archive is corrupted). Save the extracted files on your webserver. Reimport the SQL files (i.e. *.sql1, *sql2, etc.) with phpmyadmin (it is recommended to save your database first). Keep the backup files for (in days): To restore the backups, and if you have backuped only some folders, you will have to execute the following steps: Install a fresh version of Wordpress on your webserver. Replace the folders (i.e. 'plugins',  'themes', and/or 'uploads') of the root of your webserver by the extracted folders. Replace the wp-config.php (at the root of your webserver) with the extracted one. Backups Parameters Manage translations Give feedback Other plugins Date of the backup If you want to be notify when the backup process is finished, please enter your email: Backup files FTP transfer OK FTP transfer KO: %s FTP transfer reset Part %s Backup finished on %s at %s The total size of the files is %s These files will be deleted in %s days Delete these backup files The process is still in progress for this backup (begun %s at %s). Ten plugin pozwala na wykonywanie kopii zapasowej wybranych części Twojej strony! Po prostu skonfiguruj harmonogram i treści do zachowania i ciesz się: proces zostanie uruchomiony przez osobę odwiedzającą Twoją stronę! Czy chcesz wprowadzić własną nazwę dla plików? Trwa wyodrębnianie SQL (wyodrębniono %s rekordów) Tworzenie archiwum ZIP (spakowano %s plików, w %s części. Obecny rozmiar archiwum to: %s. Wysyłanie FTP jest w toku (%s plików zostało wrzuconych na FTP). Wysyłanie poczty jest w toku (%s plików zostało wysłanych). Anuluj ten proces Proszę czekać... Nie znaleziono żadnych kopii zapasowych, powinieneś poczekać albo wykonać ją teraz (patrz poniżej) Od %s sekund trwa zapis kopii zapasowej. Poczekaj %s sekund na ponowne uruchomienie Ten błąd może się pojawiać jeśli kopia jest zbyt duża. Ustaw rozmiar kopii na 1Mb i zwiększaj go stopniowo. (Wyodrębnianie SQL - kończenie) Tutaj wpisz własną nazwę (Wyodrębnianie SQL) Nie trzeba wyodrębniać SQL (Tworzę archiwum ZIP) (Wysyłanie na serwer) (Wysyłanie na serwer - kończenie) Nie trzeba wysyłać na serwer Backup z %s na %s (%s) (Wysyłanie na e-mail) Twoja instalacja Wordpress nie mogą wysyłać e-mail (zbyt duże załącznikii)! (Wysyłanie na e-mail - kończenie) Ustawiono nazwę plików %s. Nie trzeba wysyłać na e-mail Kończenie Wystąpił nieznany błąd! Nowa kopia zapasowa została utworzona! Szanowny użytkowniku, Do niniejszej wiadomości załączono %s z %s kopii zapasowej plików zaplanowanych na dzisiaj Z poważaniem, Poniżej znajduje się podsumowanie procesu wykonania kopii zapasowej Podsumowanie ogólne Backup rozpoczął się o %s i trwał %s minut(y) i %s sekund(y). %s to fraza aktualnej opcji. Możesz ustawić tą opcję na %s. Podsumowanie SQL Wyodrębnianie SQL rozpoczęło się o %s i trwało %s minut(y) i %s sekund(y). Wyodrębniono %s rekordów i zapisano je w %s plikach Utworzono %s o %s Podsumowanie pakowania Tworzenie archiwum rozpoczęło się o %s i trwało %s minut(y) i %s sekund(y). Spakowano %s plików do %s części (zip, z01, z02, itd.) Uwaga! %s plików wyłączono z kopii zapasowej, ponieważ były za duże. (Dopuszczalny rozmiar kopii to %s MB) %s (rozmiar %s) Te pliki będą dostępne przez %s dni pod tym adresem: %s to losowa fraza używana w celach bezpieczeństwa. Podsumowanie FTP %s archiwów znajduje się na serwerze %s %s zachowano w %s BŁĄD: %s nie został zapisany. Komunikat błędu: %s Kopia z %s na %s Nie zdefiniowano hosta Twoja instalacja PHP nie obsługuje funkcji protokołu SSL ... Użyj FTP zamiast FTPS! Nie można znaleźć serwera %s! Folder %s nie może zostać utworzony w repozytorium FTP Plik %s nie może zostać przesłany do repozytorium FTP %s! Funkcja ftp_put zwróciła: %s %s to rozwinięcie (np. %s). Plik %s nie może być przeniesiony do repozytorium FTP i nie można wprowadzić trybu PASV :%s Folder %s nie istnieje. Utwórz folder aby rozpocząc transfer. Login / hasło nieprawidłowe! Folder %s nie ma praw do zapisu Nie da się przełączyć w tryb PASV Wszystko jest OK! Zapisz zawartość SQL z treścią pod-witryn w innych plikach: Co chcesz zachować? Wszystkie katalogi (cała instalacja Wordpress): (np. %s) Zaznacz tę opcję jeśli chcesz zachować wszystko. Uważaj, kopia może mieć duży rozmiar. Zainstaluj/Aktywuj %s. W przeciwnym razie wtyczka nie będzię działać poprawnie! Katalog pluginów: Zaznacz tą opcje, jeśli chcesz zachować kopie wszystkich wtyczek, które zainstalowaleś i które używasz na swojej stronie. Folder kompozycji: Zaznacz tą opcje, jeśli chcesz zachować kopie wszystkich kompozycji, które zainstalowałes i które używasz na swojej stronie. Folder uploadu dla tej strony: Zaznacz tą opcje jeśli chcesz zachować kopie wszystkich zdjęc, plików które zainstalowaleś i które używasz na swojej stronie by pisać i tworzyć posty/ strony Wszystkie katalogi upload (dla tej strony i pod-blogów): Zaznacz tę opcję, jeśli chcesz, aby zapisać zdjęcia, pliki, itp., które ludzie załadowali do tworzenia artykułów / postów / stron. Folder uploadu dla strony głównej: Zaznacz tę opcję, jeśli chcesz, aby zapisać zdjęcia, pliki itd., które zostały przesłane na Twoją główną stronę do tworzenia artykułów / postów / stron. Jak często chcesz wykonywać kopię swojej strony? Folder uploadu: Baza danych SQL: Zaznacz tę opcję, jeśli chcesz zapisać tekst z Twoich postów, konfiguracje, itp. z tej strony Wszystkie bazy SQL: Zaznacz tę opcję, jeśli chcesz, aby zapisać wszystkie teksty postów, konfiguracji, itd. dla wszystkich stron w serwisie Tylko Twoja baza SQL: Zaznacz tę opcję, jeśli chcesz zapisać tekst z Twoich postów, konfiguracje itp. dla głównej strony Zaznacz tą opcje, jeśli chcesz zachować wszystkie twoje posty oraz konfiguracje, itp. Maksymalny rozmiar pliku (w MB): Należy pamiętać, że plik zip będzie podzielony na kilka plików aby nie przekroczyć maksymalnego rozmiaru pliku, który ustawiono Częstotliwość (dni): Chcesz aby kopie zapasowe były wysyłane emailem? Wyślij backup na email: Jeśli tak, podaj adres email: Czy chcesz dodać przyrostek (sufix) do wysłanych plików: Ta opcja umożliwia obejśćie zabezpieczeń niektórych dostawców kont emailowych, którzy domyślnie blokują emaile wraz załącznikami z rozszerzeniem zip ( np. GMail.) Nie musisz wypełniać tego pola, jeśli nie skonfigurowałeś opcji email. Czy chcesz zapisać ten backup na FTP? Zapisać pliki backupu na FTP? serwer FTP: Użyj formy %s lub %s Czas wykonania kopii: Jeśli %s zostanie pominięty, zostanie on automatycznie dodany podczas łączenia się z serwerem FTP. Jest to przydatne, jeśli pojawi się błąd 404 z parametrem %s. port FTP: Domyślny port to %s Twój login FTP: Twoje hasło FTP: Kliknij na ten przycisk %s, aby sprawdzić, czy powyższe informacje są poprawne Test Stwórz podfolder z datą w swoim repozytorium FTP dla wszystkich plików kopii zapasowych: Dodaj prefix do stworzonego folderu: Twoja instalacja PHP nie obsługuje funkcji FTP, ponieważ ta opcja jest wyłączona! Przepraszamy ... Pamiętaj, że 0 oznacza północ, 1 oznacza 1. w nocy, 13am 1. po południu, itd. Backup nastąpi o (czas zgodny z godziną serwera), wiec upewnij sie, że twoja strona nie jest przeciążona w tych godzinach. Zaawansowane - Zarządzanie czasem i użyciem pamięci Jaki jest maksymalny rozmiar przydzielonej pamięci ( w MB): Niektóre aplikacje Wordpressa moga kolidować z pamiecia, więc staraj sie zminimalizowac jej ilość jeśli napotykasz takie problemy. Limit ilośći pamieci serwera wynosi %s, podczas gdy obecne zużycie pamięci to %s. Zaleca się aby maksymalna wielkość załącznika nie była większa niż ta wartość. Pamiętaj, że pliki większe niż ten limit nie zostaną uwzględnione w pliku zip! Jaki jest maksymalny czas na wypakowanie plików php (w sekundach): Nawet jeśli nie masz limitu czasowego, zalecamy by ustawić wartość czas na 15s by uniknać problemów związanych z przetwarzaiem skryptów php przez twego dostawce. Tutaj znajdują się pliki archiwum. Możesz wykonać nową kopię zapasową teraz lub ściągnać starsze pliki arichiwum. Proszę pamiętać, że obecny czas GMT z serwera to %s. Jeśli nie jest prawidłowy, należy skonfigurować instalację Wordpress poprawnie. Należy również pamiętać, że kopia zapasowa nie będzie końca dokładnie w tym czasie. Proces wykonywania kopii zapasowej może potrwać do 6h zwłaszcza jeśli nie masz dużo ruchu na swojej stronie i / lub jeśli kopia zapasowa jest dość duża. Automatyczna kopia plików zostanie uruchomiona w %s dni i %s godzin. Proces wykonywania kopii zapasowej rozpoczęto %s godzin temu, ale jeszcze nie jest skończony. Wymuś nowy backup (na mail/FTP) Wymuś nowy backup (bez wysyłania na mail/FTP) Jak przywrócić pliki kopii zapasowej? Aby przywrócić kopie zapasowe, jeśli masz archiwizowane pełną instalację, musisz wykonać następujące kroki: Zapisz wszystkie pliki zip (np.: *.zip, *.z01, *.z02, itp.) w folderze na Twoim dysku. Rozpakuj te pliki za pomocą IZArc, WinZip lub WinRAR (oprogramowanie inne mogłyby nie wspierać wieloczęściowych archiwów i zgłosić, że archiwum jest uszkodzone). Zapisz wyodrębnione pliki na swoim serwerze. Zaimportuj pliki SQL (np. *. SQL1, * SQL2 itp.) z phpMyAdmin (zaleca się, aby zachować swoją bazę danych na początku). Trzymaj kopie zapasową przez (dni): Aby przywrócić kopie zapasowe, jeśli masz zarchiwizowane tylko niektóre foldery, musisz wykonać następujące kroki: Zainstaluj nową wersję Wordpressa na swoim serwerze. Zastąp foldery (tj. &quot;plugins&quot;, &quot;themes&quot; i / lub &quot;uploads&quot;) z głównego katalogu serwera WWW Twoimi wypakowanymi folderami. Zastąp wp-config.php (w katalogu głównym serwera internetowego) wypakowanym z archiwum. Archiwa Ustawienia Zarządzaj tłumaczeniami Wyślij nam swoją opinię Inne pluginy Data kopii zapasowej Jeśli chcesz otrzymać maila potwierdzającego wykonanie kopii zapasowej, wpisz tutaj swój adres Zarchiwizowane pliki Transfer FTP OK Transfer FTP KO: %s Transfer FTP zresetowany Część %s Zakończono backup na %s z %s Całkowita wielkość kopii %s Te pliki zostaną skasowane za %s dni Skasuj kopie zapasowe pllików Proces jest w toku dla kopii zapasowej (rozpoczęte %s na %s). 