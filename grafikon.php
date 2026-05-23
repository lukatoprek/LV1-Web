<?php
require_once 'config/session.php';
$pageTitle  = 'Glazba - Grafikoni';
$activePage = 'grafikon';
$extraCss   = 'style/style_grafikon.css';
include 'includes/header.php';
?>

	<main role="main" class="grafikon-main">

		<!-- Naslov stranice -->
		<section class="content" aria-labelledby="grafikon-naslov">
			<h1 id="grafikon-naslov">Vizualizacija glazbenih podataka</h1>
			<p>Statisti&#269;ki prikaz &#382;anrova iz popisa pjesama na temelju tablice s po&#269;etne stranice.</p>
		</section>

		<section class="content grafikon-sekcija histogram-sekcija" aria-labelledby="histogram-naslov" role="region">
			<h2 id="histogram-naslov">Glazbena analiza &ndash; Histogram</h2>
			<p class="grafikon-opis">Broj pjesama po &#382;anru iz popisa na po&#269;etnoj stranici.</p>

			<div class="histogram-wrapper" role="img" aria-label="Histogram koji prikazuje broj pjesama po zanru: Rock 7, Metal 4, Pop 2, Jazz 2, Ostalo 5">
				<div class="y-os" aria-hidden="true">
					<span>10</span>
					<span>8</span>
					<span>6</span>
					<span>4</span>
					<span>2</span>
					<span>0</span>
				</div>
				<div class="bars-area" aria-hidden="true">
					<div class="bar-container">
						<div class="bar" style="--bar-visina: 70%; --bar-boja: var(--graf-boja-1);" aria-label="Rock: 7 pjesama">
							<span class="bar-vrijednost">7</span>
						</div>
						<div class="bar-label">Rock</div>
					</div>
					<div class="bar-container">
						<div class="bar" style="--bar-visina: 40%; --bar-boja: var(--graf-boja-2);" aria-label="Metal: 4 pjesme">
							<span class="bar-vrijednost">4</span>
						</div>
						<div class="bar-label">Metal</div>
					</div>
					<div class="bar-container">
						<div class="bar" style="--bar-visina: 20%; --bar-boja: var(--graf-boja-3);" aria-label="Pop: 2 pjesme">
							<span class="bar-vrijednost">2</span>
						</div>
						<div class="bar-label">Pop</div>
					</div>
					<div class="bar-container">
						<div class="bar" style="--bar-visina: 20%; --bar-boja: var(--graf-boja-4);" aria-label="Jazz: 2 pjesme">
							<span class="bar-vrijednost">2</span>
						</div>
						<div class="bar-label">Jazz</div>
					</div>
					<div class="bar-container">
						<div class="bar" style="--bar-visina: 50%; --bar-boja: var(--graf-boja-5);" aria-label="Ostalo: 5 pjesama">
							<span class="bar-vrijednost">5</span>
						</div>
						<div class="bar-label">Ostalo</div>
					</div>
				</div>
			</div>
		</section>

		<section class="content grafikon-sekcija pie-sekcija" aria-labelledby="pie-naslov" role="region">
			<h2 id="pie-naslov">Glazbena analiza &ndash; Pie Chart</h2>
			<p class="grafikon-opis">Udio &#382;anrova u ukupnom popisu pjesama.</p>

			<div class="pie-wrapper">
				<svg class="pie-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"
					 role="img" aria-label="Kruzni grafikon zanrova: Rock 35%, Metal 20%, Pop 10%, Jazz 10%, Ostalo 25%">
					<title>Kru&#382;ni grafikon glazbenih &#382;anrova</title>

					<path class="pie-kri&#353;ka" d="M100,100 L100,10 A90,90 0 0,1 175.5,145 Z"
						  fill="var(--graf-boja-1)"
						  style="--dx: 6px; --dy: -6px;"
						  aria-label="Rock 35%">
						<title>Rock: 35%</title>
					</path>

					<path class="pie-kri&#353;ka" d="M100,100 L175.5,145 A90,90 0 0,1 71.6,185.6 Z"
						  fill="var(--graf-boja-2)"
						  style="--dx: 6px; --dy: 6px;"
						  aria-label="Metal 20%">
						<title>Metal: 20%</title>
					</path>

					<path class="pie-kri&#353;ka" d="M100,100 L71.6,185.6 A90,90 0 0,1 27.2,162.1 Z"
						  fill="var(--graf-boja-3)"
						  style="--dx: -6px; --dy: 6px;"
						  aria-label="Pop 10%">
						<title>Pop: 10%</title>
					</path>

					<path class="pie-kri&#353;ka" d="M100,100 L27.2,162.1 A90,90 0 0,1 10,100 Z"
						  fill="var(--graf-boja-4)"
						  style="--dx: -8px; --dy: 0px;"
						  aria-label="Jazz 10%">
						<title>Jazz: 10%</title>
					</path>

					<path class="pie-kri&#353;ka" d="M100,100 L10,100 A90,90 0 0,1 100,10 Z"
						  fill="var(--graf-boja-5)"
						  style="--dx: -6px; --dy: -6px;"
						  aria-label="Ostalo 25%">
						<title>Ostalo: 25%</title>
					</path>
				</svg>

				<div class="pie-legenda" role="list" aria-label="Legenda grafikona">
					<div class="legenda-stavka" role="listitem">
						<span class="legenda-boja" style="background: var(--graf-boja-1);"></span>
						Rock (35%)
					</div>
					<div class="legenda-stavka" role="listitem">
						<span class="legenda-boja" style="background: var(--graf-boja-2);"></span>
						Metal (20%)
					</div>
					<div class="legenda-stavka" role="listitem">
						<span class="legenda-boja" style="background: var(--graf-boja-3);"></span>
						Pop (10%)
					</div>
					<div class="legenda-stavka" role="listitem">
						<span class="legenda-boja" style="background: var(--graf-boja-4);"></span>
						Jazz (10%)
					</div>
					<div class="legenda-stavka" role="listitem">
						<span class="legenda-boja" style="background: var(--graf-boja-5);"></span>
						Ostalo (25%)
					</div>
				</div>
			</div>
		</section>

	</main>

<?php include 'includes/footer.php'; ?>
