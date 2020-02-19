# CharMap
<img src="https://img.shields.io/badge/dle-14.0-007dad.svg"> <img src="https://img.shields.io/badge/lang-tr,en-ce600f.svg"> <img src="https://img.shields.io/badge/license-MIT-60ce0f.svg">

Charmap, sitenizde ekli olan makaleleri ilk harflerine göre gruplandırarak dinamik haritasını oluşturmanıza yarar.
Sitenizdeki yazarlar için de harita oluşturabilirsiniz. Üstelik bu harita sayfaları profillerinde görüntülenebilir.
Her yazar için eklediği yazılar kendi profilinde listelenir.

## Kurulum

**1)** .htaccess dosyasını açarak satırlarını bulun: 

```
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^([^/]+).html$ index.php?do=static&page=$1&seourl=$1 [L]
```

Üstüne ekleyin:

```bash
# CharMap
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^([^/]+)([^.]+)*.html$ index.php?do=charmap&name=$1&args=$2 [L]
# CharMap
```



## Konfigürasyon

Modül ile ilgili tüm ayarları yönetici panelinden yapabilirsiniz.



## Ekran Görüntüleri

![Ekran 1](/docs/screen1.png?raw=true)
![Ekran 2](/docs/screen2.png?raw=true)
![Ekran 3](/docs/screen3.png?raw=true)

## Tarihçe

| Version | Tarih | Uyumluluk | Yenilikler |
| ------- | ----- | --------- | ---------- |
|**1.4**|19.02.2018|14.0, 13.x|DLE 13.x ve 14.0 uyumluluğu sağlandı. Plugin sistemine geçildi|
|**1.3**|01.02.2018|12.0, 12.1|DLE 12.1 uyumluluğu sağlandı.|
|**1.2**|18.12.2015|10.2, 10.3|İlave alanlardan bilgi çekme özelliği eklendi<br>Profildeki bulunamadı hatası giderildi<br>Tagı profil dışındaki sayfalarda gözükmesi hatası giderildi `[on-user] ... [/on-user]`<br>mb_substr var / yok kontrolü eklendi.|
|**1.1**|11.05.2014|10.2, 10.3|Kullanıcı haritası seçeceği eklendi|
|**1.0**|20.04.2014|10.2, 10.3|İlk sürüm|
