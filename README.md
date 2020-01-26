# Image Resizer
<img src="https://img.shields.io/badge/dle-13.0+-007dad.svg"> <img src="https://img.shields.io/badge/lang-tr-ce600f.svg"> <img src="https://img.shields.io/badge/lang-en-ce600f.svg"> <img src="https://img.shields.io/badge/license-MIT-60ce0f.svg">

Image Resizer modülü sitede kullandığınız resimlerin boyutlarını yükleme işlemi sonrasında kolayca değiştirebilmenizi sağlar. Örneğin sitenizin tasarımı gereği aynı makaleleri birkaç farklı yerde, içerdiği resimi farklı boyutlarda göstereceksiniz. Bu durumda ikisinden biri boyutsal olarak bulunduğu yere uyumsuz olacaktır. Kapladığı alan 300x250 px kendisi 500x400 px gibi. Sonuç olarak bu durum SEO açısından faydalı olmayacaktır. Çünkü sayfa açılış hızını düşürecek, sayfa boyutunu arttıracak vs. nedenlerle arama motorları tarafından sevilmeyecektir.

**Başlıca Özellikleri :**
SEO etkisinin yanında site geliştiricileri için de çok faydalı bir modüldür. Örnek bir haber sitesinde aynı haber birkaç yerde farklı boyutlarda görüntülenmek istendiğinde, resmi css/html olarak o alanı kaplayacak şekilde ayarlansa bile en boy oranı bozulacaktır. Crop (kesme) özelliği sayesinde resmin istediğiniz bölümünü otomatik olarak istediğiniz boyutta kesitirebilirsiniz.

Yine farklı bir özellik olarak; İlave alan olarak girdiğiniz dış linkler, yani herhangi bir resim upload servisine yüklenen resimleri de otomatik olarak indirip üzerinde boyutlandırma, kalite ayarlama, kesme işlemleri yaptırabilirsiniz.

DLE'de sevilmeyen durumlardan biri olan: Yüklenen resimlere unix timestamp öneki eklenmesi, resmin adını uzattığı gibi SEO açısından da faydalı olmadığı öngörülebilir. Modüldeki diğer bir özellik te alt tagında belirtilen yazıyı resim adı olarak kullanabilmedir. Bu özellik sayesinde yüklenen resimleri otomatik olarak adlandırabilirsiniz.

Resimlerin resize edilmesi işlemi sürekli olmadığından sitenizin açılış hızı düşmeyecektir. Çünkü resim ilk görüntüleme işlemi esnasında boyutlandırılır, kesilir vs. işlemleri yapılır ve önbelleklenir. Bu sayede her seferinde aynı işlemler uygulanmaz.

Resimler yeniden adlandırıldığında bile url'si hala uzun oluyor ise /uploads/cache/ dizinini örneğin: /resimler/ vb. şekilde sahte bir dizin ile değiştirip resim linklerini olabildiğince kısaltabilirsiniz. Burada dikkat etmeniz gereken husus: yazdığınız sahte dizinin sistemde kullanılan bir dizin olmaması ( Kategori isimleri ve dosya sistemi dizinleri )

Resimleri yeniden boyutlandırdığınızda otomatik olarak width ve height değerini de html koduna ekletebilirsiniz. Bu özelliğin de SEO açısından faydalı olduğunu söyleyebiliriz.



**Örnek bir çıktı :**
TPL Kodu

```
<img src="{image-1}" resize="w:300,h:300,q:90" alt="{title}" />
```



HTML çıktısı ( Modülsüz ) *Resim boyutu: Admin panelde ayarlanan medium resim boyutu*

```markup
<img src="/uploads/posts/2015-11/medium/1448828412_1200x630.png" alt="Agar.io Private Server Açıldı">
```


HTML çıktısı ( Modül ile ) *Resim boyutu: 140x100 px ve Kalite: 90%*

```markup
<img width="140" height="100" src="/resimler/agario-private-server-acildi-ef2bb658.png" alt="Agar.io Private Server Açıldı">
```


HTML çıktısı ( Modül ile ) *Otomatik isimlendirme kullanılmamış halde*

```markup
<img src="/uploads/cache/1d466324cce4fa5e23793284e1b5aee3.jpg" alt="Agar.io Private Server Açıldı">
```


Dış bağlantılardaki resimleri otomatik olarak sunucunuza indirebilirsiniz. d:1 parametresi ile bunu yapabilirsiniz.

```markup
<img src="http://i.imgur.com/bPCSY43.jpg" alt="ucifer - Saison 2" resize="w:210,h:295,d:1,e:w">
```


Sonuç olarak

```markup
<img src="/uploads/cache/1d466324cce4fa5e23793284e1b5aee3.jpg" alt="ucifer - Saison 2">
```



*94 KB olan resim 12 KB'a düşürülmüş oldu. Sitenizde birçok resim olduğunu düşünürsek sayfa boyutu olması gerekenden çok daha fazla olacaktır.*

**Modülü her tpl dosyanızda kullanabilirsiniz. İlave alan veya image tagları ile uyumludur. İstediğiniz zaman açıp kapatabilirsiniz.**



*Eğer sitenizin açılış hızından memnun değilseniz ve farklı boyutlarda resimler kullanıyosanız modülü kullanarak
 Google Page Speed de iki durum arasındaki farkı  görebilirsiniz.*
*Daha fazla optimizasyon için google page speed'in verdiği optimize edilmiş dosyaları da kullanabilir.*
*/uploads/cache Klasöründeki resimleri indirip optipng gibi optimizer programlar ile  boyutlarını daha da ufaltıp geri yükleyebilirsiniz.*



**Kullanım Desenleri**

```markup
resize="w:728|h:440|q:75|e:1"
resize="w:728;h:440;q:75;e:1"
resize="w:728,h:440,q:75,e:1"
resize="w:728;h:440;q:75;e:w,f:1"
resize="w:728;h:440;q:75;e:h"
```


Crop ( Kesme ) ile. Bu işlem için resim, otomatik olarak kesme işlemine uygun olabilecek en küçük boyuta indirgenir ve kırpılır.

```markup
resize="w:400,h:400,q:80" crop="c-l"  # Orta-Orta
resize="w:400,h:400,q:80" crop="t-l"  # Üst-Sol
resize="w:400,h:400,q:80" crop="b-r"  # Alt-Sağ
```



Parametrelerin açıklamaları

```
w: Width ( genişlik )
h: Height ( yükseklik )
q: Quality ( kalite 0-100 )
f: w ve h ile belirtilen genişlik değerleri resme width ve height olarak eklenir.
d: Eğer src kısmında dış bir bağlantı girilmişse ve ayarlarda açık ise, resim sunucuya indirilir.
e: Edge ( kenar hangi kenara göre boyutlandırılacağı )
- 1 ya da w : Genişlik
- 2 ya da h : Yükseklik
- 0 ya da boş : En Uzun Kenar
```

**Uyarı !**
Hash metodu olarak admin panelden, sunucunuzun desteklediği bir metodu seçip kullanabilirsiniz. Resimlerin sonuna eklenen sonek için  kullanılmaktadır.
Yukarıdaki resim örneğinde :
agario-private-server-acildi-**ef2bb658**.png



**FTP Yüklemeleri**
1.5 Sürümünde Lokal ve Uzak sunucuya FTP yükleme özelliği eklendi. Bu  özellik ile boyutlandırma/kesme işlemi sonrasında resimlerin lokal veya  uzak bir sunucuya yüklenmesini sağlayabilirsiniz. Böylece sitenizin  bulunduğu host resimler nedeniyle hemen dolmayacaktır. Admin panelinde,  işlenen resimlerin lokal sunucuda ne kadar yer kapladığını  görebilirsiniz.



**1.7 Sürümüne Güncelleme**
Güncelleme  talimatı arşivde verilmiştir. Bu güncelleme ile, DLE'nin sunmuş olduğu  resim ilave özelliği ile uyumluluk sağlanmıştır. Ayrı bir etiket ile  xfvalue değeri URL'ye dönüştürülüyor sonrasında resizer modülü devreye  giriyor.
Örnek kodlar:

```markup
<img src="{img:[xfvalue_resim1]}" resize="w:320,h:320,q:90" alt="{title}" />
<img src="{img:[xfvalue_resim2]}" resize="w:480,h:480,q:50" alt="{title}" />
```


Buradaki  etiketi  içerisine tıklanabilir veya tıklanamaz olarak girilen html den
Eğer tıklanabilir ise **a[href]**'i, tıklanamaz ise **img[src]** değerini çekerek resmi otomatik bulur.



## Tarihçe

| Version | Tarih | Uyumluluk | Yenilikler |
| ------- | ----- | --------- | ---------- |
| **1.9** | 02.08.2018 | DLE 13.0+ | DLE 13.0 uyumlu plugin haline getirildi. |
| **1.8.1** | 06.09.2017 | DLE 12.0 | DLE 12.0 için admin panel tasarımı yenilendi |
| **1.8** | 16.07.2017 | DLE 11.3, 11.2, 11.1, 11.0, 10.x | PHP düzeltmeleri yapıldı.<br>Yerel / Uzak sunucu yüklemeleri için mantıksal hata giderildi.<br>FTP önbelleğini tek tıklama ile temizleme özelliği eklendi. |
| **1.7** | 02.04.2017 | DLE 11.2, 11.1, 11.0, 10.x | DLE 11.2 ve diğer yeni sürümler için bazı düzeltmeler yapıldı.<br>Yeni PHP sürümleri için ufak düzeltmeler yapıldı.<br>DLE Resim ilave alanı için resize desteği eklendi. |
| **1.6** | 05.10.2016 | DLE 11.1, 11.0, 10.x | Resim indirildi mi kontrolü yaparak hatalı ise resmin linkini değiştirmeyecek.<br>Resim indirme fonksiyonuna "follow action" özelliği dahil edildi. Böylece bilinen resim servislerinden resim indirmesi mümkün hale geldi. |
| **1.5** | 16.09.2016 | DLE 11.1, 11.0, 10.x | Resmin dosya adı olarak img'nin hangi attr'sinin kullanılacağı seçilebilir<br>Resim genişlik ve yükseklik bilgisinin belirtilen/gerçek değerinin girilmesi ayarlanabilir hale getirildi. ( f parametresi )<br>Lokal subdomain yükleme özelliği eklendi.<br>Uzak sunucu (FTP) yükleme özelliği eklendi.<br>Admin panelden yönetilebilir hale getirildi.<br>Dil dosyası kullanımına geçildi.<br>Otomatik kurulum desteği eklendi. |
| **1.4** | 25.10.2015 | DLE 10.6, 10.5, 10.4 | Resim boyutlandırma ve kesme özelliği.<br>Resim önbellekleme.<br>Alt tagını okuyarak otomatik adlandırma.<br>Dış linkleri indirebilme özelliği.<br>Resimlere otomatik olarak width ve height değeri ekleme.<br>Linkleri kısaltma için sahte link yazım imkanı. |



*Thanks for SimpleImage library..*

```
* @package     SimpleImage class
* @version     2.5.5
* @author      Cory LaViska for A Beautiful Site, LLC (http://www.abeautifulsite.net/)
* @author      Nazar Mokrynskyi <nazar@mokrynskyi.com> - merging of forks, namespace support, PhpDoc editing, adaptive_resize() method, other fixes
* @license     This software is licensed under the MIT license: http://opensource.org/licenses/MIT
* @copyright   A Beautiful Site, LLC
```