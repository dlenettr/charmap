<style>
.charmap { background: #fff; padding: 10px; }
.charmap p { margin: 10px 0 !important; background: #eee; border-radius: 2px; padding: 10px 10px; }
.charmap h4 { font-size: 18px; margin: 10px 0; color: #555; }
.charmap .charlist {  margin-bottom: 10px; padding: 10px 5px; text-align: center; background: #ededed; }
.charmap .charlist a { padding: 2px 3.6px; margin: 1px; text-align: center; border-radius: 2px; background: #3394e6; color: #fff; }
.charmap .charlist a:hover { background: #467ca8; text-decoration: none; }
.charmap .newslist { border: 3px solid #fafafa; font-size: 15px; margin: 10px 0; color: #555; padding: 5px; }
.charmap .newslist .alpha { margin-top: 15px; margin-bottom: 5px; }
.charmap .newslist .alpha a { padding: 3px 10px; background: #3394e6; color: #fff; border-radius: 2px; }
.charmap .newslist a { color: #555; transition: .3s; }
.charmap .newslist a:hover { color: #467ca8; text-decoration: none; margin-left: 5px; transition: .3s; }
</style>

<div class="charmap">

	[charlist]
	<div class="charlist">
		[char]<a href="{char-link}" title="{char}">{char}</a>&nbsp;[/char]
		<div class="clr"></div>
	</div>
	[/charlist]
	<p>
	[on-char]
		Karakter Sayfası<br />
		Sayfa Başlığı : {title}<br />
		Sayfa Açıklaması: {description}<br />
		Sayfa URL'si : {url}<br />
	[/on-char]

	[on-main]
		Ana Sayfa<br />
		Sayfa Başlığı : {title}<br />
		Sayfa Açıklaması: {description}<br />
		Sayfa URL'si : {url}<br />
	[/on-main]

	[on-map]
		Sitemap Page<br />
		Sayfa Başlığı : {title}<br />
		Sayfa Açıklaması: {description}<br />
		Sayfa URL'si : {url}<br />
	[/on-map]

	[on-user]
		Kullanıcı Sayfası<br />
		Kullanıcı Adı: {user}<br />
		Kullanıcı Linki: {user-link}<br />
	[/on-user]
	</p>

	[contentlist]
		<div class="newslist">
		Sayfadaki toplam: {total}<br />
		Genel Toplam: {total-items}<br />
		Geçerli Sayfa: {page-current}<br />
		Toplam Sayfa Sayısı: {page-total}<br />

		<!-- List of content (loop)-->
		[content]

			[alpha]<div class="alpha"><a href="{alpha-link}">{alpha}</a></div>[/alpha] <!-- Sadece ilk karakter değiştiğinde görülür -->

			<a href="{full-link}">{title}</a><br />

		[/content]

		</div>
	[/contentlist]

	{navigator}

</div>