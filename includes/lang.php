<?php
/**
 * Translation / bilingual helper
 *
 * Usage:  t('nav.players')  →  "Players" (en) or "Chwaraewyr" (cy)
 * Language is stored in $_SESSION['lang'] — 'en' or 'cy'.
 * Set via public/setlang.php?lang=cy
 */

declare(strict_types=1);

function currentLang(): string
{
    return (isset($_SESSION['lang']) && $_SESSION['lang'] === 'cy') ? 'cy' : 'en';
}

function isWelsh(): bool
{
    return currentLang() === 'cy';
}

/**
 * Return a translated string.
 * Falls back to English if the Welsh key is missing.
 */
function t(string $key): string
{
    static $strings = null;

    if ($strings === null) {
        $strings = translations();
    }

    $lang = currentLang();

    return $strings[$lang][$key]
        ?? $strings['en'][$key]
        ?? $key; // last resort: return the key itself
}

function translations(): array
{
    return [
        'en' => [
            // Navigation
            'nav.home'         => 'Home',
            'nav.players'      => 'Players',
            'nav.fixtures'     => 'Fixtures',
            'nav.results'      => 'Results',
            'nav.history'      => 'Club History',
            'nav.club'         => 'The Club',
            'nav.contact'      => 'Contact',
            'nav.welsh'        => 'Cymraeg',
            'nav.english'      => 'English',

            // Hero
            'hero.sub'         => 'Fifty years of rugby in Porthmadog.',
            'hero.btn_fixtures'=> 'View Fixtures',
            'hero.btn_squad'   => 'Our Squad',

            // Homepage sections
            'home.latest'      => 'Latest Result',
            'home.no_results'  => 'No results recorded yet.',
            'home.next'        => 'Next Fixture',
            'home.no_fixtures' => 'No upcoming fixtures.',
            'home.anniversary' => '50th Anniversary',
            'home.half_century'=> 'Half a century of rugby in Porthmadog.',
            'home.all_results' => 'All Results →',
            'home.full_list'   => 'Full Fixture List →',
            'home.history_link'=> 'Our History →',
            'home.about'       => 'About the Club',
            'home.about_p1'    => 'Porthmadog RFC has been going since',
            'home.about_p1b'   => '. We\'re a community rugby club based at the Traeth, competing in the North Wales leagues.',
            'home.about_p2'    => 'Players of all levels are welcome — whether you\'ve played for years or just want to give it a go. On a match day you\'ll find the usual mix of people who love the game and don\'t take themselves too seriously.',
            'home.years_pill'  => 'Years',
            'home.members_pill'=> 'Members',
            'home.senior_pill' => 'Senior',
            'home.full_history'=> 'Read Our Full History →',
            'home.squad'       => 'The Squad',
            'home.all_players' => 'View All Players →',
            'home.anniversary_title' => '50 Years of Porthmadog RFC',

            // Players page
            'players.title'    => 'The Squad',
            'players.sub'      => 'Meet the players who wear the Porthmadog RFC jersey with pride',
            'players.count'    => 'Players',
            'players.positions'=> 'Positions',
            'players.highest'  => 'Highest #',
            'players.empty'    => 'No players listed yet',
            'players.empty_sub'=> 'Check back soon — the squad will be added shortly.',

            // Player profile
            'player.back'      => '← Back to Squad',
            'player.not_found' => 'Player not found.',
            'player.position'  => 'Position',
            'player.number'    => 'Squad Number',
            'player.age'       => 'Age',
            'player.club'      => 'Club',
            'player.profile'   => 'Player Profile',

            // Fixtures page
            'fixtures.title'   => 'Fixtures',
            'fixtures.sub'     => 'Upcoming matches — dates, opponents, and venues',
            'fixtures.upcoming'=> 'Upcoming Fixtures',
            'fixtures.past'    => 'Recent Past Fixtures',
            'fixtures.date'    => 'Date & Time',
            'fixtures.opponent'=> 'Opponents',
            'fixtures.venue'   => 'Venue',
            'fixtures.comp'    => 'Competition',
            'fixtures.empty'   => 'No upcoming fixtures',
            'fixtures.empty_sub'=> 'Check back soon — the season schedule will be posted here.',

            // Results page
            'results.title'    => 'Results',
            'results.sub'      => 'Season record — Porthmadog RFC vs the opposition',
            'results.heading'  => 'Match Results',
            'results.won'      => 'Won',
            'results.drawn'    => 'Drawn',
            'results.lost'     => 'Lost',
            'results.for'      => 'Points For',
            'results.against'  => 'Points Against',
            'results.date'     => 'Date',
            'results.match'    => 'Match',
            'results.score'    => 'Score',
            'results.result'   => 'Result',
            'results.venue'    => 'Venue',
            'results.comp'     => 'Competition',
            'results.report'   => 'Match Report:',
            'results.empty'    => 'No results yet this season',
            'results.empty_sub'=> 'Results will appear here after matches are played.',
            'results.win'      => 'Win',
            'results.loss'     => 'Loss',
            'results.draw'     => 'Draw',
            'results.motm'     => 'Man of the Match',
            'results.motm_sub' => 'Most recent award',

            // Venues / locations
            'venue.home'       => 'Home',
            'venue.away'       => 'Away',

            // History page
            'history.title'    => 'Club History',
            'history.sub'      => 'Fifty years of rugby in Porthmadog',
            'history.p1'       => 'Porthmadog RFC was founded in 1976 by a group of local people who wanted to play rugby in the town. There was no grand plan — just enough players willing to give it a go and somewhere to play. The club has been based at the Traeth ever since, one of the more distinctive grounds in North Wales with the Snowdonia hills behind you and the estuary out to the side.',
            'history.p2'       => 'Over the years the faces have changed but the club has stayed the same in all the ways that matter. Players join as youngsters and end up staying for decades. Some move away and come back. Others just never leave. That continuity is what keeps a small club like this going.',
            'history.p3'       => 'We compete in the North Wales leagues and we\'ve had our fair share of good seasons and bad ones. We\'re not here to pretend otherwise. What we are is a club that\'s genuinely part of the community — the kind of place where you\'ll recognise every face at the bar after a game, whether you won or lost.',
            'history.p4'       => 'Fifty years is a long time for a rugby club of any size to still be going. For a club in a town like Porthmadog, it\'s something the whole community should be proud of.',
            'history.achieve'  => 'A Few Things Worth Mentioning',
            'history.achieve1' => 'Fifty years of unbroken rugby in Porthmadog since 1976',
            'history.achieve2' => 'Regular competitors in the North Wales regional leagues',
            'history.achieve3' => 'A youth section that has introduced generations of local players to the game',
            'history.achieve4' => 'A clubhouse and ground that has hosted hundreds of home matches at the Traeth',
            'history.achieve5' => 'A club that\'s still here — and still going',
            'history.timeline' => 'Timeline',
            'history.t1976'    => 'Club founded. First games played at the Traeth ground in Porthmadog.',
            'history.t1980s'   => 'The club establishes itself in the North Wales leagues. A regular match day crowd starts to build.',
            'history.t1990s'   => 'Clubhouse improvements made. Youth rugby begins to take shape, bringing younger players through.',
            'history.t2000s'   => 'The club continues to compete in regional competition. Some strong seasons, some tough ones.',
            'history.t2010s'   => 'Ground and facilities updated. The club remains a fixture in the local rugby calendar.',
            'history.t2025'    => 'Porthmadog RFC marks 50 years. Half a century of rugby in this town.',
            'history.anniv_heading' => '50th Anniversary',

            // Footer
            'footer.rights'    => 'All rights reserved.',
            'footer.admin'     => 'Admin Login',
            'footer.celebrating' => 'Celebrating 50 Years',

            // Club page (coaches, committee, training)
            'club.title'            => 'The Club',
            'club.sub'              => 'Coaches, committee, and training information',
            'club.training'         => 'Training',
            'club.training_sub'     => 'Open to all — new faces always welcome',
            'club.training_days'    => 'Tuesday & Thursday',
            'club.training_time'    => '7:00pm',
            'club.training_venue'   => 'The Traeth, Porthmadog',
            'club.training_all'     => 'All levels welcome — whether you\'ve played for years or just want to give it a go.',
            'club.coaches'          => 'Coaches & Management',
            'club.coaches_empty'    => 'Coaching staff will be listed here soon.',
            'club.committee'        => 'Club Committee',
            'club.committee_empty'  => 'Committee members will be listed here soon.',

            // Contact page
            'contact.title'         => 'Contact',
            'contact.sub'           => 'Get in touch with Porthmadog RFC',
            'contact.ground'        => 'The Ground',
            'contact.address'       => 'The Traeth, Porthmadog, Gwynedd',
            'contact.training_h'    => 'Training Times',
            'contact.email_h'       => 'Email',
            'contact.phone_h'       => 'Phone',
            'contact.no_contact'    => 'Contact details to be added — ask the admin to update them.',
            'contact.find_us'       => 'Find Us',

            // Calendar
            'cal.training'          => 'Training 7pm',
            'cal.prev'              => '&#8592; Prev',
            'cal.next'              => 'Next &#8594;',
            'cal.mon'               => 'Mon',
            'cal.tue'               => 'Tue',
            'cal.wed'               => 'Wed',
            'cal.thu'               => 'Thu',
            'cal.fri'               => 'Fri',
            'cal.sat'               => 'Sat',
            'cal.sun'               => 'Sun',

            // Stats / leaderboard
            'stats.season'          => 'Season',
            'stats.top_scorers'     => 'Top Try Scorers',
            'stats.top_motm'        => 'Most MOTM Awards',
            'stats.tries'           => 'Tries',
            'stats.assists'         => 'Assists',
            'stats.motm'            => 'MOTM',
            'stats.no_stats'        => 'No stats recorded for this season yet.',
            'stats.player_stats'    => 'Season Stats',

            // Nav - combined fixtures/results page
            'nav.results'      => 'Fixtures & Results',

            // Brand
            'brand.est'        => 'Est.',
        ],

        'cy' => [
            // Navigation
            'nav.home'         => 'Hafan',
            'nav.players'      => 'Chwaraewyr',
            'nav.fixtures'     => 'Amserlen',
            'nav.results'      => 'Canlyniadau',
            'nav.history'      => 'Hanes y Clwb',
            'nav.club'         => 'Y Clwb',
            'nav.contact'      => 'Cysylltu',
            'nav.welsh'        => 'Cymraeg',
            'nav.english'      => 'English',

            // Hero
            'hero.sub'         => 'Hanner can mlynedd o rygbi ym Mhorthmadog.',
            'hero.btn_fixtures'=> 'Gweld Amserlen',
            'hero.btn_squad'   => 'Ein Sgwad',

            // Homepage
            'home.latest'      => 'Canlyniad Diweddaraf',
            'home.no_results'  => 'Dim canlyniadau wedi\'u cofnodi eto.',
            'home.next'        => 'Gêm Nesaf',
            'home.no_fixtures' => 'Dim gemau sydd i ddod.',
            'home.anniversary' => 'Pen-blwydd yn 50',
            'home.half_century'=> 'Hanner canrif o rygbi ym Mhorthmadog.',
            'home.all_results' => 'Pob Canlyniad →',
            'home.full_list'   => 'Rhestr Gemau Llawn →',
            'home.history_link'=> 'Ein Hanes →',
            'home.about'       => 'Am y Clwb',
            'home.about_p1'    => 'Mae Clwb Rygbi Porthmadog wedi bod yn mynd ers',
            'home.about_p1b'   => '. Rydym yn glwb rygbi cymunedol yn y Traeth, yn cystadlu yng nghynghreiriau Gogledd Cymru.',
            'home.about_p2'    => 'Mae croeso i chwaraewyr o bob lefel — p\'un a ydych chi wedi chwarae ers blynyddoedd neu eisiau rhoi cynnig arni. Ar ddiwrnod gêm fe welwch y cymysgedd arferol o bobl sy\'n caru\'r gêm ac heb gymryd eu hunain ormod o ddifrif.',
            'home.years_pill'  => 'Mlynedd',
            'home.members_pill'=> 'Aelodau',
            'home.senior_pill' => 'Uwch',
            'home.full_history'=> 'Darllen Ein Hanes Llawn →',
            'home.squad'       => 'Y Sgwad',
            'home.all_players' => 'Gweld Pob Chwaraewr →',
            'home.anniversary_title' => '50 Mlynedd o Glwb Rygbi Porthmadog',

            // Players
            'players.title'    => 'Y Sgwad',
            'players.sub'      => 'Cwrdd â\'r chwaraewyr sy\'n gwisgo crys Clwb Rygbi Porthmadog â balchder',
            'players.count'    => 'Chwaraewyr',
            'players.positions'=> 'Safleoedd',
            'players.highest'  => 'Rhif Uchaf',
            'players.empty'    => 'Dim chwaraewyr wedi\'u rhestru eto',
            'players.empty_sub'=> 'Dewch yn ôl yn fuan — bydd y sgwad yn cael ei ychwanegu cyn hir.',

            // Player profile
            'player.back'      => '← Nôl i\'r Sgwad',
            'player.not_found' => 'Chwaraewr heb ei ganfod.',
            'player.position'  => 'Safle',
            'player.number'    => 'Rhif Sgwad',
            'player.age'       => 'Oedran',
            'player.club'      => 'Clwb',
            'player.profile'   => 'Proffil Chwaraewr',

            // Fixtures
            'fixtures.title'   => 'Amserlen',
            'fixtures.sub'     => 'Gemau sydd i ddod — dyddiadau, gwrthwynebwyr, a lleoliadau',
            'fixtures.upcoming'=> 'Gemau Sydd i Ddod',
            'fixtures.past'    => 'Gemau Diweddar y Gorffennol',
            'fixtures.date'    => 'Dyddiad ac Amser',
            'fixtures.opponent'=> 'Gwrthwynebwyr',
            'fixtures.venue'   => 'Lleoliad',
            'fixtures.comp'    => 'Cystadleuaeth',
            'fixtures.empty'   => 'Dim gemau sydd i ddod',
            'fixtures.empty_sub'=> 'Dewch yn ôl yn fuan — bydd yr amserlen yn cael ei phostio yma.',

            // Results
            'results.title'    => 'Canlyniadau',
            'results.sub'      => 'Cofnod y tymor — Clwb Rygbi Porthmadog yn erbyn y gwrthwynebiad',
            'results.heading'  => 'Canlyniadau Gemau',
            'results.won'      => 'Wedi Ennill',
            'results.drawn'    => 'Cyfartal',
            'results.lost'     => 'Wedi Colli',
            'results.for'      => 'Pwyntiau O Blaid',
            'results.against'  => 'Pwyntiau yn Erbyn',
            'results.date'     => 'Dyddiad',
            'results.match'    => 'Gêm',
            'results.score'    => 'Sgôr',
            'results.result'   => 'Canlyniad',
            'results.venue'    => 'Lleoliad',
            'results.comp'     => 'Cystadleuaeth',
            'results.report'   => 'Adroddiad Gêm:',
            'results.empty'    => 'Dim canlyniadau eto y tymor hwn',
            'results.empty_sub'=> 'Bydd canlyniadau\'n ymddangos yma ar ôl i gemau gael eu chwarae.',
            'results.win'      => 'Buddugoliaeth',
            'results.loss'     => 'Colled',
            'results.draw'     => 'Cyfartal',
            'results.motm'     => 'Dyn y Gêm',
            'results.motm_sub' => 'Gwobr ddiweddaraf',

            // Venues
            'venue.home'       => 'Cartref',
            'venue.away'       => 'Oddi Cartref',

            // History
            'history.title'    => 'Hanes y Clwb',
            'history.sub'      => 'Hanner can mlynedd o rygbi ym Mhorthmadog',
            'history.p1'       => 'Sefydlwyd Clwb Rygbi Porthmadog ym 1976 gan grŵp o bobl leol oedd eisiau chwarae rygbi yn y dref. Doedd dim cynllun mawr — dim ond digon o chwaraewyr parod i roi cynnig arni a rhywle i chwarae. Mae\'r clwb wedi bod yn seiliedig ar y Traeth byth ers hynny — un o feysydd mwyaf nodedig Gogledd Cymru, gyda mynyddoedd Eryri tu ôl i chi a\'r aber o\'r ochr arall.',
            'history.p2'       => 'Dros y blynyddoedd mae\'r wynebau wedi newid ond mae\'r clwb wedi aros yr un fath yn yr holl ffyrdd sy\'n bwysig. Mae chwaraewyr yn ymuno\'n ifanc ac yn aros am ddegawdau. Mae rhai\'n symud i ffwrdd ac yn dod yn ôl. Mae eraill byth yn gadael. Dyna\'r parhad sy\'n cadw clwb bach fel hwn i fynd.',
            'history.p3'       => 'Rydym yn cystadlu yng nghynghreiriau Gogledd Cymru ac rydym wedi cael ein cyfran deg o dymhorau da a drwg. Nid yma i dwyllo mohonon ni. Yr hyn ydym ni yw clwb sy\'n rhan wirioneddol o\'r gymuned — y math o le lle byddwch chi\'n adnabod pob wyneb wrth y bar ar ôl gêm, p\'un a enniloch chi neu golli.',
            'history.p4'       => 'Mae hanner can mlynedd yn amser hir i unrhyw glwb rygbi i barhau. I glwb mewn tref fel Porthmadog, mae\'n rhywbeth y dylai\'r gymuned gyfan fod yn falch ohono.',
            'history.achieve'  => 'Rhai Pethau Sy\'n Werth eu Crybwyll',
            'history.achieve1' => 'Hanner can mlynedd o rygbi di-dor ym Mhorthmadog ers 1976',
            'history.achieve2' => 'Cystadleuwyr rheolaidd yng nghynghreiriau rhanbarthol Gogledd Cymru',
            'history.achieve3' => 'Adran ieuenctid sydd wedi cyflwyno cenedlaethau o chwaraewyr lleol i\'r gêm',
            'history.achieve4' => 'Clwbdy a maes sydd wedi cynnal cannoedd o gemau cartref yn y Traeth',
            'history.achieve5' => 'Clwb sy\'n dal yma — ac yn dal i fynd',
            'history.timeline' => 'Llinell Amser',
            'history.t1976'    => 'Sefydlwyd y clwb. Chwaraewyd y gemau cyntaf yn y Traeth ym Mhorthmadog.',
            'history.t1980s'   => 'Mae\'r clwb yn sefydlu ei hun yng nghynghreiriau Gogledd Cymru. Mae tyrfa ddiwrnod gêm rheolaidd yn dechrau adeiladu.',
            'history.t1990s'   => 'Gwnaed gwelliannau i\'r clwbdy. Mae rygbi ieuenctid yn dechrau ffurfio, gan ddod â chwaraewyr iau drwodd.',
            'history.t2000s'   => 'Mae\'r clwb yn parhau i gystadlu mewn cystadlaethau rhanbarthol. Rhai tymhorau cryf, rhai anodd.',
            'history.t2010s'   => 'Diweddarwyd y maes a\'r cyfleusterau. Mae\'r clwb yn parhau yn rhan o galendr rygbi lleol.',
            'history.t2025'    => 'Mae Clwb Rygbi Porthmadog yn nodi 50 mlynedd. Hanner can mlynedd o rygbi yn y dref hon.',
            'history.anniv_heading' => 'Pen-blwydd yn 50',

            // Footer
            'footer.rights'    => 'Cedwir pob hawl.',
            'footer.admin'     => 'Mewngofnodi Gweinyddwr',
            'footer.celebrating' => 'Dathlu 50 Mlynedd',

            // Club page
            'club.title'            => 'Y Clwb',
            'club.sub'              => 'Hyfforddwyr, pwyllgor, a gwybodaeth ymarfer',
            'club.training'         => 'Ymarfer',
            'club.training_sub'     => 'Agored i bawb — croeso i wynebau newydd bob amser',
            'club.training_days'    => 'Dydd Mawrth a Dydd Iau',
            'club.training_time'    => '7:00yh',
            'club.training_venue'   => 'Y Traeth, Porthmadog',
            'club.training_all'     => 'Croeso i bob lefel — p\'un a ydych chi wedi chwarae ers blynyddoedd neu eisiau rhoi cynnig arni.',
            'club.coaches'          => 'Hyfforddwyr a Rheolaeth',
            'club.coaches_empty'    => 'Bydd staff hyfforddi\'n cael eu rhestru yma cyn hir.',
            'club.committee'        => 'Pwyllgor y Clwb',
            'club.committee_empty'  => 'Bydd aelodau\'r pwyllgor yn cael eu rhestru yma cyn hir.',

            // Contact page
            'contact.title'         => 'Cysylltu',
            'contact.sub'           => 'Cysylltwch â Chlwb Rygbi Porthmadog',
            'contact.ground'        => 'Y Cae',
            'contact.address'       => 'Y Traeth, Porthmadog, Gwynedd',
            'contact.training_h'    => 'Amseroedd Ymarfer',
            'contact.email_h'       => 'E-bost',
            'contact.phone_h'       => 'Ffôn',
            'contact.no_contact'    => 'Manylion cyswllt i\'w hychwanegu — gofynnwch i\'r gweinyddwr eu diweddaru.',
            'contact.find_us'       => 'Dewch o Hyd i Ni',

            // Calendar
            'cal.training'          => 'Ymarfer 7yh',
            'cal.prev'              => '&#8592; Blaenorol',
            'cal.next'              => 'Nesaf &#8594;',
            'cal.mon'               => 'Llun',
            'cal.tue'               => 'Maw',
            'cal.wed'               => 'Mer',
            'cal.thu'               => 'Iau',
            'cal.fri'               => 'Gwe',
            'cal.sat'               => 'Sad',
            'cal.sun'               => 'Sul',

            // Stats / leaderboard
            'stats.season'          => 'Tymor',
            'stats.top_scorers'     => 'Prif Sgorwyr Ceisiau',
            'stats.top_motm'        => 'Mwyaf o Wobrau Dyn y Gêm',
            'stats.tries'           => 'Ceisiau',
            'stats.assists'         => 'Cyfraniadau',
            'stats.motm'            => 'DyG',
            'stats.no_stats'        => 'Dim ystadegau wedi\'u cofnodi ar gyfer y tymor hwn eto.',
            'stats.player_stats'    => 'Ystadegau\'r Tymor',

            // Nav - combined fixtures/results page
            'nav.results'      => 'Amserlen a Chanlyniadau',

            // Brand
            'brand.est'        => 'Sefydlwyd',
        ],
    ];
}
