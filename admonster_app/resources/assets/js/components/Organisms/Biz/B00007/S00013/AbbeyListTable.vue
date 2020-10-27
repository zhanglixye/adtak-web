<template>
    <div class="pa-6">
        <div class="project-function-box">
            <span v-show="!accomplish" title="素材を追加" @click="addProject"><svg id="addProject" class="operationProject" width="22" height="22"><g transform="matrix(1 0 0 1 -35 -134 )"><path d="M 4.4 22  L 4.4 19.8  L 6.6 19.8  L 6.6 22  L 4.4 22  Z M 0 6.6  L 0 4.4  L 2.2 4.4  L 2.2 6.6  L 0 6.6  Z M 0 11  L 0 8.8  L 2.2 8.8  L 2.2 11  L 0 11  Z M 4.4 2.2  L 4.4 0  L 0 0  L 0 2.2  L 4.4 2.2  Z M 15.4 2.2  L 15.4 0  L 17.6 0  L 17.6 2.2  L 15.4 2.2  Z M 11 2.2  L 11 0  L 13.2 0  L 13.2 2.2  L 11 2.2  Z M 0 17.6  L 0 19.8  L 0 22  L 2.2 22  L 2.2 17.6  L 0 17.6  Z M 6.6 2.2  L 6.6 0  L 8.8 0  L 8.8 2.2  L 6.6 2.2  Z M 0 15.4  L 0 13.2  L 2.2 13.2  L 2.2 15.4  L 0 15.4  Z M 19.8 13.2  L 19.8 11  L 22 11  L 22 13.2  L 19.8 13.2  Z M 19.8 17.6  L 19.8 15.4  L 22 15.4  L 22 17.6  L 19.8 17.6  Z M 22 0  L 19.8 0  L 19.8 4.4  L 22 4.4  L 22 0  Z M 19.8 8.8  L 19.8 6.6  L 22 6.6  L 22 8.8  L 19.8 8.8  Z M 8.8 22  L 8.8 19.8  L 11 19.8  L 11 22  L 8.8 22  Z M 22 19.8  L 17.6 19.8  L 17.6 22  L 22 22  L 22 19.8  Z M 13.2 22  L 13.2 19.8  L 15.4 19.8  L 15.4 22  L 13.2 22  Z M 12.1 12.1  L 17.6 12.1  L 17.6 9.9  L 12.1 9.9  L 12.1 4.4  L 9.9 4.4  L 9.9 9.9  L 4.4 9.9  L 4.4 12.1  L 9.9 12.1  L 9.9 17.6  L 12.1 17.6  L 12.1 12.1  Z " fill-rule="nonzero" fill="#7f7f7f" stroke="none" transform="matrix(1 0 0 1 35 134 )" /></g></svg></span>
            <span v-show="!accomplish" title="素材を削除" @click="dleProject"><svg id="delProject" class="operationProject" width="22" height="22"><g transform="matrix(1 0 0 1 -72 -134 )"><path d="M 4.4 22  L 4.4 19.8  L 6.6 19.8  L 6.6 22  L 4.4 22  Z M 0 6.6  L 0 4.4  L 2.2 4.4  L 2.2 6.6  L 0 6.6  Z M 0 11  L 0 8.8  L 2.2 8.8  L 2.2 11  L 0 11  Z M 4.4 2.2  L 4.4 0  L 0 0  L 0 2.2  L 4.4 2.2  Z M 15.4 2.2  L 15.4 0  L 17.6 0  L 17.6 2.2  L 15.4 2.2  Z M 11 2.2  L 11 0  L 13.2 0  L 13.2 2.2  L 11 2.2  Z M 0 17.6  L 0 19.8  L 0 22  L 2.2 22  L 2.2 17.6  L 0 17.6  Z M 6.6 2.2  L 6.6 0  L 8.8 0  L 8.8 2.2  L 6.6 2.2  Z M 0 15.4  L 0 13.2  L 2.2 13.2  L 2.2 15.4  L 0 15.4  Z M 19.8 13.2  L 19.8 11  L 22 11  L 22 13.2  L 19.8 13.2  Z M 19.8 17.6  L 19.8 15.4  L 22 15.4  L 22 17.6  L 19.8 17.6  Z M 22 0  L 19.8 0  L 19.8 4.4  L 22 4.4  L 22 0  Z M 19.8 8.8  L 19.8 6.6  L 22 6.6  L 22 8.8  L 19.8 8.8  Z M 8.8 22  L 8.8 19.8  L 11 19.8  L 11 22  L 8.8 22  Z M 22 19.8  L 17.6 19.8  L 17.6 22  L 22 22  L 22 19.8  Z M 13.2 22  L 13.2 19.8  L 15.4 19.8  L 15.4 22  L 13.2 22  Z M 12.1 12.1  L 17.6 12.1  L 17.6 9.9  L 12.1 9.9  L 9.9 9.9  L 4.4 9.9  L 4.4 12.1  L 9.9 12.1  L 12.1 12.1  Z " fill-rule="nonzero" fill="#7f7f7f" stroke="none" transform="matrix(1 0 0 1 72 134 )" /></g></svg></span>
            <span title="暗号化される添付ファイルを解凍する" @click="popupZip"><svg id="decompressionProject" class="operationProject" width="22" height="26"><g transform="matrix(1 0 0 1 -109 -130 )"><path d="M 20.3863314180533 3.85505875219691  C 20.3863314180533 1.95642777860805  18.6501464792327 0.417283267411999  16.5084538202621 0.417283267411999  C 16.5056345617009 0.417283267411999  16.5028153039781 0.417285992938393  16.4999960521224 0.417291443986314  C 14.3631761151899 0.437553189329037  12.6365255646313 1.96824537437425  12.613669921875 3.86255663339121  L 12.613669921875 8.13168959430462  C 12.6365255646313 10.0260008533216  14.3631761151899 11.5566930383668  16.4999960521224 11.5769547837095  C 18.6368159938876 11.5566930442901  20.3634665518662 10.0260008575415  20.3863221947455 8.13168959430462  L 20.3863221947455 3.86255663339121  C 20.3863283436155 3.86005734337474  20.3863314180533 3.85755804815781  20.3863314180533 3.85505875219691  Z M 18.6966811775244 3.92241577720889  C 18.6966811775244 3.92742847557335  18.6966591753067 3.93244115496316  18.696615171371 3.93745370153357  L 18.696615171371 8.20658666244698  C 18.6855487017012 8.98971900349682  18.1560287817442 9.6938556965139  17.3448495699975 10.0041163198061  L 17.3448495699975 10.3786016714893  L 15.655142546623 10.3786016714893  L 15.655142546623 10.0041163198061  C 14.8439633300341 9.69385569982487  14.3144434046991 8.98971900610296  14.3033769328737 8.20658666244698  L 14.3033769328737 3.93745370153357  C 14.3258821223485 2.87034970683717  15.2962819952093 2.0100809620837  16.4999960521224 1.99012989691838  C 16.5056503021275 1.99009088700962  16.5113047019561 1.99007138183368  16.5169591231879 1.99007138183368  C 17.7207863726678 1.99007138183368  18.6966811775244 2.85521143622218  18.6966811775244 3.92241577720889  Z M 21.9609856320645 13  L 21.9609856320645 5.41666666666667  L 11.0390070883893 5.41666666666667  L 11.0390070883893 13  L 21.9609856320645 13  Z M 15.7198550324136 9.1922078167729  C 15.2377219152825 8.89596726638766  14.9388777501435 8.33240620042484  14.9397137103142 7.72101538147994  C 14.9813206409015 6.79906781518289  15.6678453636301 6.07558692263572  16.4999963545129 6.07674148619411  C 17.3617166757575 6.07674148619411  18.0602790101397 6.85165358914887  18.0602790101397 7.80755611147632  C 18.0830705007739 8.42530167344085  17.7779451733147 9.0007077708464  17.2801376880402 9.27874854676932  L 17.2801376880402 11.4422668347106  L 15.7198550324136 11.4422668347106  L 15.7198550324136 9.1922078167729  Z M 17.1034807477679 12.188949773341  C 17.2795758928571 12.0241751965182  17.2692173549107 11.7594250337578  17.0848353794642 11.6020560558932  C 17.0579031808036 11.5872448579764  17.0123256138392 11.5465140637057  16.937744140625 11.4798636730806  C 16.8175851004464 11.3798880871432  16.6870675223214 11.2725069022473  16.5399762834821 11.1484631196953  C 16.1235630580358 10.8096569673514  15.7154366629464 10.4689994152682  15.3176688058036 10.1468558605806  C 15.2720912388392 10.1135306652681  15.2720912388392 10.1135306652681  15.2244419642857 10.0727998709973  C 14.6961565290179 9.65068073037227  14.2610979352679 9.30261757933069  13.9379115513393 9.05453001422644  C 13.7535295758929 8.91382363401819  13.5960797991071 8.80644244912235  13.4924944196429 8.732386459539  C 13.4179129464286 8.68239866657023  13.3619768415179 8.64907347125775  13.29775390625 8.62500527464309  C 13.2231724330358 8.59168007933061  13.1589494977678 8.57501748167441  13.0574358258929 8.57501748167441  C 12.7984723772321 8.5824230806327  12.5933733258929 8.76571165485148  12.5933733258929 8.99713662229944  L 12.5933733258929 12.7221528983411  L 16.7616489955357 12.7221528983411  L 16 12.3074393566744  L 16 24.7303316092785  L 16.7616489955357 24  L 4.72295619419647 24  C 3.95435267857141 24.3156180676119  3.33284040178569 23.7601981457369  4 23.0733288423514  L 4 9.0137992199556  L 2.86877790178575 9.42851276162227  L 11.2032575334821 9.42851276162227  C 11.4622209821429 9.42851276162227  11.6673200334821 9.24707558714319  11.6673200334821 9.0137992199556  C 11.6673200334821 8.78237425250768  11.4642926897321 8.59908567828898  11.2032575334821 8.59908567828898  L 3 8.59908567828898  L 3 23.0733288423514  C 2.40678710937496 24.2156424816744  3.44471261160716 25.1431937512056  4.72295619419647 24.7962962962963  L 17 24.7962962962963  L 17 11.9112398124035  L 13.0574358258929 11.9112398124035  L 13.5194266183036 12.3259533540702  L 13.5194266183036 9.0137992199556  C 13.5194266183036 9.23781858844527  13.3163992745536 9.41185016396602  13.0740094866071 9.41925576292435  C 12.9911411830357 9.41925576292435  12.9434919084821 9.41185016396602  12.8979143415179 9.38593056761194  C 12.8896275111607 9.38593056761194  12.9165597098214 9.39333616657019  12.9434919084821 9.40999876422644  C 13.0263602120536 9.46739215615352  13.1651646205358 9.5581107433931  13.3329729352679 9.69141152464319  C 13.6478724888393 9.93209349078902  14.0746442522321 10.2708996431327  14.5925711495536 10.6837617850598  C 14.6381487165179 10.7170869803723  14.6381487165179 10.7170869803723  14.6857979910714 10.7578177746431  C 15.0752790178571 11.0725557303723  15.4916922433036 11.411361882716  15.8998186383929 11.7501680350599  C 16.0489815848214 11.8668062186536  16.1774274553571 11.9815930025078  16.2975864955357 12.0815685884452  C 16.37216796875 12.1389619803724  16.4177455357143 12.1815441743828  16.4467494419642 12.2056123709973  C 16.6311314174108 12.3629813488619  16.9273856026786 12.3537243501639  17.1034807477679 12.188949773341  Z M 3.34319893973213 16.041712631414  L 2.4171456473214 16.041712631414  L 2.4171456473214 16.0491182303723  L 0.565039062500017 16.0491182303723  L 0.565039062500017 23  L 19.0861049107143 23  L 19.0861049107143 16  L 3.34319893973213 16  L 3.34319893973213 16.041712631414  Z M 8.40023716517861 17.1229300793306  L 8.40023716517861 17.6450248058931  L 5.66765485491071 21.1793469087578  L 8.48310546875004 21.1793469087578  L 8.48310546875004 21.8328910168306  L 4.56550641741069 21.8328910168306  L 4.56550641741069 21.3033906913099  L 7.28773018973216 17.7857311861015  L 4.58415178571431 17.7857311861015  L 4.58415178571431 17.1229300793306  L 8.40023716517861 17.1229300793306  Z M 9.32629045758923 17.1247814790703  L 10.2336983816964 17.1247814790703  L 10.2336983816964 21.8328910168306  L 9.32629045758923 21.8328910168306  L 9.32629045758923 17.1247814790703  Z M 12.268115234375 20.0796154634452  L 12.268115234375 20.0870210624036  L 12.2701869419643 20.0870210624036  L 12.2701869419643 21.8328910168306  L 11.3524204799108 21.8328910168306  L 11.3524204799108 17.1247814790703  L 13.370263671875 17.1247814790703  C 13.9627720424108 17.1247814790703  14.4268345424107 17.2654878592785  14.7686662946429 17.5376436209973  C 15.110498046875 17.8116507824556  15.2865931919642 18.174525131414  15.2865931919642 18.6225638683931  C 15.2865931919642 19.0854138032889  15.1208565848214 19.4427339530286  14.776953125 19.6982271170911  C 14.4351213727679 19.9555716808931  13.9627720424108 20.0796154634452  13.3516183035714 20.0796154634452  L 12.268115234375 20.0796154634452  Z M 12.2805454799108 17.7783255871431  L 12.2805454799108 19.4260713553723  L 12.2701869419643 19.4260713553723  L 13.3723353794643 19.4260713553723  C 13.6955217633929 19.4260713553723  13.9461983816964 19.3594209647474  14.1140066964286 19.2187145845389  C 14.2797433035714 19.0780082043307  14.3729701450893 18.8873140311536  14.3729701450893 18.6225638683931  C 14.3729701450893 18.3652193045911  14.2901018415179 18.1671195324556  14.1140066964286 18.009750554591  C 13.9379115513393 17.8523815767264  13.6975934709821 17.7783255871431  13.4013392857142 17.7783255871431  L 12.2805454799108 17.7783255871431  Z " fill-rule="nonzero" fill="#7f7f7f" stroke="none" transform="matrix(1 0 0 1 109 130 )" /></g></svg></span>
            <span title="素材全件ダウンロード" @click="downloadAllMaterial"><svg id="downloadAll" class="operationProject" width="24" height="22"><g transform="matrix(1 0 0 1 -146 -134 )"><path d="M 14.73 17.2686746987952  C 14.5050000000001 16.8710843373494  13.98 16.8710843373494  13.6800000000001 17.2686746987952  L 12.78 18.3620481927711  L 12.78 9.9132530120482  C 12.78 9.3168674698795  12.405 8.91927710843373  12.03 8.91927710843373  C 11.5799999999999 8.91927710843373  11.28 9.41626506024096  11.28 9.9132530120482  L 11.28 18.3620481927711  L 10.3800000000001 17.2686746987952  C 10.0799999999999 16.8710843373494  9.55500000000006 16.8710843373494  9.25500000000011 17.2686746987952  C 8.95499999999993 17.666265060241  8.95499999999993 18.2626506024096  9.25500000000011 18.6602409638554  L 11.5050000000001 21.5427710843373  C 11.5799999999999 21.6421686746988  11.655 21.7415662650603  11.73 21.7415662650603  C 12.03 21.9403614457831  12.3299999999999 21.8409638554217  12.5550000000001 21.5427710843373  L 14.73 18.6602409638554  C 15.03 18.2626506024096  15.03 17.666265060241  14.73 17.2686746987952  Z M 23.9318181818182 11.6666666666667  C 23.9318181818182 9.00000000000001  22.0568181818182 6.66666666666667  19.5 6.00000000000001  C 18.8181818181818 2.58333333333333  15.75 0  12 0  C 8.25 0  5.18181818181824 2.58333333333333  4.5 6.00000000000001  C 1.94318181818176 6.66666666666667  0.0681818181817562 9.00000000000001  0.0681818181817562 11.6666666666667  C 0.0681818181817562 14.9166666666667  2.71022727272725 17.5  6.03409090909099 17.5  L 6.88636363636374 17.5  L 6.88636363636374 15.8333333333333  L 6.03409090909099 15.8333333333333  C 3.64772727272725 15.8333333333333  1.77272727272725 14  1.77272727272725 11.6666666666667  C 1.77272727272725 9.33333333333335  3.64772727272725 7.49999999999999  6.03409090909099 7.49999999999999  C 6.03409090909099 4.25000000000001  8.67613636363626 1.66666666666665  12 1.66666666666665  C 15.3238636363637 1.66666666666665  17.965909090909 4.25000000000001  17.965909090909 7.49999999999999  C 20.3522727272727 7.49999999999999  22.2272727272727 9.33333333333335  22.2272727272727 11.6666666666667  C 22.2272727272727 14  20.3522727272727 15.8333333333333  17.965909090909 15.8333333333333  L 17.1136363636363 15.8333333333333  L 17.1136363636363 17.5  L 17.965909090909 17.5  C 21.2897727272727 17.5  23.9318181818182 14.9166666666667  23.9318181818182 11.6666666666667  Z " fill-rule="nonzero" fill="#7f7f7f" stroke="none" transform="matrix(1 0 0 1 146 134 )" /></g></svg></span>
            <div class="jump-box">
                <a href="https://abbey.yahoo.co.jp/babbey/index.html" target="_blank" class="jump">Abbeyツールを開く</a>
                <svg class="jump-icon" width="14" height="14"><g transform="matrix(1 0 0 1 -891 -141 )"><path d="M 12.9659090909091 3.51988636363636  L 12.9659090909091 1.64090465198864  L 5.98890269886364 8.61791104403409  C 5.79476207386364 8.81205166903409  5.47996715198864 8.81205166903409  5.28581099076705 8.61791104403409  C 5.09167036576705 8.4237548828125  5.09167036576705 8.10897549715909  5.28581099076705 7.9148193359375  L 12.1665394176136 1.03409090909091  L 10.4801136363636 1.03409090909091  C 10.2055419921875 1.03409090909091  9.98295454545455 0.811503462357954  9.98295454545455 0.536931818181818  C 9.98295454545455 0.262360174005682  10.2055419921875 0.0397727272727273  10.4801136363636 0.0397727272727273  L 13.4630681818182 0.0397727272727273  C 13.7376398259943 0.0397727272727273  13.9602272727273 0.262360174005682  13.9602272727273 0.536931818181818  L 13.9602272727273 3.51988636363636  C 13.9602272727273 3.7944580078125  13.7376398259943 4.01704545454545  13.4630681818182 4.01704545454545  C 13.188496537642 4.01704545454545  12.9659090909091 3.7944580078125  12.9659090909091 3.51988636363636  Z M 12.9659090909091 12.8416193181818  L 12.9659090909091 7  L 12.9659090909091 6.70481178977273  C 12.9659090909091 6.43024014559659  13.188496537642 6.20765269886364  13.4630681818182 6.20765269886364  C 13.7376398259943 6.20765269886364  13.9602272727273 6.43024014559659  13.9602272727273 6.70481178977273  L 13.9602272727273 13.4630681818182  C 13.9602272727273 13.7376398259943  13.7376398259943 13.9602272727273  13.4630681818182 14  L 0.536931818181818 14  C 0.262360174005682 13.9602272727273  0.0397727272727273 13.7376398259943  0 13.4630681818182  L 0 0.536931818181818  C 0.0397727272727273 0.262360174005682  0.262360174005682 0.0397727272727273  0.536931818181818 0.0397727272727273  L 7.21750710227273 0.0397727272727273  C 7.49207874644886 0.0397727272727273  7.71466619318182 0.262360174005682  7.71466619318182 0.536931818181818  C 7.71466619318182 0.811503462357954  7.49207874644886 1.03409090909091  7.21750710227273 1.03409090909091  L 1.15838068181818 1.03409090909091  C 1.08973733578097 1.03409090909091  1.03409090909091 1.08973733578097  1 1.15838068181818  L 1 12.8416193181818  C 1.03409090909091 12.910262664219  1.08973733578097 12.9659090909091  1.15838068181818 13  L 12.8416193181818 13  C 12.910262664219 12.9659090909091  12.9659090909091 12.910262664219  12.9659090909091 12.8416193181818  Z " fill-rule="nonzero" fill="#0BB3B7" stroke="none" transform="matrix(1 0 0 1 891 141 )" /></g></svg>
            </div>
        </div>
        <table class="project-list-box" cellspacing="0">
            <thead>
            <tr class="project-list-title">
                <td :class="{checkMore:!projectListMore}" class="check">
                    <input class="checkAll" type="checkbox" @click="selectAll" :checked="selectAllState" :disabled="accomplish">
                </td>
                <td :class="{materialMore:!projectListMore}" class="material">画像素材</td>
                <td :class="{resultMore:!projectListMore}" class="result">結果キャプチャー</td>
                <td :class="{judgeMore:!projectListMore}" class="judge">判定</td>
                <td class="abbeyId" :class="{abbeyIdMore:!projectListMore}">AbbyID</td>
                <td class="MenuName" :class="{MenuNameMore:!projectListMore}">メニュー名</td>
                <td class="errCenter" v-if="!projectListMore">エラー表示</td>
                <td class="inspectionResult" v-if="!projectListMore">チェック結果</td>
            </tr>
            </thead>
            <draggable
                ref="parentNode"
                class="project-list"
                tag="tbody"
                v-model="projectList"
                v-bind="dragOptions"
                @start="drag = true"
                @end="drag = false"
            >
                <transition-group type="transition" :name="!drag ? 'flip-list' : null">
                    <tr v-for="(item,index) in projectList" v-bind:key="item.webProperties.indexVal" :data-value="index">
                        <td :class="{checkMore:!projectListMore}" class="check">
                            <input type="checkbox" class="checkState" @click="select" :checked="item.webProperties.projectCheck" :disabled="accomplish">
                        </td>
                        <td :class="{materialMore:!projectListMore}" class="material">
                            <div class="img-box" v-show="item.webProperties.materialShade">
                                <img
                                    v-if="item.webProperties.materialThumbnailShow"
                                    ref="materialThumbnail"
                                    :src="item.webProperties.materialThumbnail"
                                    alt=""
                                >
                                <img v-else src="/images/biz/b00007/load.gif" alt="">
                            </div>
                            <div class="info-box" v-show="item.webProperties.materialShade" @click="previewPopup(index,'material')">
                                <p>{{item.material.check_file_name}}</p>
                                <p>{{item.material.display_size}}</p>
                                <p>{{item.material.file_size}}&nbsp;Byte</p>
                            </div>
                            <div class="file-box matFile" v-show="!item.webProperties.materialShade">
                                <input type="file" @change="fileChange($event,item,'material',index)" accept=".mp4,.jpg,.jpeg,.png,.gif">
                            </div>
                        </td>
                        <td :class="{resultMore:!projectListMore}" class="result">
                            <div class="img-box" v-show="item.webProperties.resultShade">
                                <img
                                    v-if="item.webProperties.resultThumbnailState&&item.webProperties.resultThumbnailShow"
                                    ref="resultThumbnail"
                                    :src="item.webProperties.resultThumbnail"
                                    alt=""
                                >
                                <img v-else src="/images/biz/b00007/load.gif" alt="">
                            </div>
                            <div class="info-box" v-show="item.webProperties.resultShade" @click="previewPopup(index,'result')">
                                <p>{{item.result_capture.file_name}}</p>
                                <p>{{item.result_capture.display_size}}</p>
                                <p>{{item.result_capture.file_size}}&nbsp;Byte</p>
                            </div>
                            <div class="file-box resFile" v-show="!item.webProperties.resultShade">
                                <input type="file" @change="fileChange($event,item,'result_capture',index)" accept=".mp4,.jpg,.jpeg,.png,.gif" :disabled="accomplish">
                            </div>
                        </td>
                        <td
                            class="judge"
                            :class="{textRed:item.check.result===0,textGreen:item.check.result===1,textGray:item.check.result===2,judgeMore:!projectListMore}"
                        >
                            {{item.check.result===0?"NG":item.check.result===1?"OK":item.check.result===2?"不明あり":"未判定"}}
                        </td>
                        <td class="abbeyId" :class="{abbeyIdMore:!projectListMore}"><span>{{item.check.abbey_id}}</span></td>
                        <td class="MenuName" :class="{MenuNameMore:!projectListMore}"><span>{{item.check.menu_name}}</span></td>
                        <td class="errCenter" v-if="!projectListMore"><span>{{item.check.error_message}}</span></td>
                        <td class="inspectionResult" v-if="!projectListMore"><span>{{item.check.result_comment}}</span></td>
                        <div class="Details" @click="switchModule(item)">
                            <svg width="10px" height="18px"><g transform="matrix(1 0 0 1 -895 -253 )"><path d="M 9.82817869415807 8.58517034068136  C 9.94272623138603 8.70541082164329  10 8.8436873747495  10 9  C 10 9.1563126252505  9.94272623138603 9.29458917835671  9.82817869415807 9.41482965931864  L 1.8213058419244 17.8196392785571  C 1.70675830469645 17.939879759519  1.57502863688431 18  1.42611683848797 18  C 1.27720504009164 18  1.1454753722795 17.939879759519  1.03092783505155 17.8196392785571  L 0.171821305841924 16.9178356713427  C 0.0572737686139748 16.7975951903808  0 16.6593186372745  0 16.503006012024  C 0 16.3466933867735  0.0572737686139748 16.2084168336673  0.171821305841924 16.0881763527054  L 6.92439862542955 9  L 0.171821305841924 1.91182364729459  C 0.0572737686139748 1.79158316633266  0 1.65330661322645  0 1.49699398797595  C 0 1.34068136272545  0.0572737686139748 1.20240480961924  0.171821305841924 1.08216432865731  L 1.03092783505155 0.180360721442886  C 1.1454753722795 0.0601202404809613  1.27720504009164 0  1.42611683848797 0  C 1.57502863688431 0  1.70675830469645 0.0601202404809613  1.8213058419244 0.180360721442886  L 9.82817869415807 8.58517034068136  Z " fill-rule="nonzero" fill="#ffffff" stroke="none" transform="matrix(1 0 0 1 895 253 )" /></g></svg>
                        </div>
                    </tr>
                </transition-group>
            </draggable>
        </table>
        <popupPreview ref="previewPopup" :loadingDisplay="loadingDisplay"></popupPreview>
        <popupZip
            ref="popupZip"
            :loadingDisplay="loadingDisplay"
            :formData="formData"
            :initData="initData"
            :projectArr="projectList"
            :accomplish="accomplish"
        ></popupZip>
    </div>
</template>

<script>
import popupPreview from './popupPreview';
import popupZip from './popupZip';
import draggable from 'vuedraggable'
export default {
    name: 'AbbeyListTable',
    components:{
        popupPreview,
        popupZip,
        draggable
    },
    props:{
        projectListMore:Boolean,
        loadingDisplay: { type: Function, require: true },
        formData: { type: FormData, require: true },
        initData:Object,
        accomplish:Boolean
    },
    data(){
        return {
            projectList:[],
            selectAllState:false,
            restrict_file_type:['mp4','jpeg','png','gif'],
            upload_state:false,
            drag: false
        }
    },
    computed: {
        dragOptions() {
            return {
                animation: 200,
                group: 'description',
                disabled: false,
                ghostClass: 'ghost'
            };
        }
    },
    created(){
        this.initializeMaterialList();
        this.shadeShowHid();
    },
    updated(){
        this.shadeShowHid();
    },
    methods:{
        //初始化素材列表
        initializeMaterialList:function(){
            this.projectList = JSON.parse(this.initData.task_result_info.content).item_array;
            this.projectList.forEach((item,index)=>{
                this.$set(item,'webProperties',{
                    projectCheck:false,
                    resultShade:null,
                    materialShade:null,
                    indexVal:index,
                    resultThumbnailState:true,
                    resultThumbnailShow:true,
                    materialThumbnailShow:true,
                    materialThumbnail:'',
                    resultThumbnail:''
                })
            });
            this.loadMaterialThumbnail();
            this.loadResultThumbnail();
        },
        //加载素材列表的缩略图
        async loadMaterialThumbnail(itemVal) {
            try {
                if (itemVal == null) {
                    for (var i = 0; i < this.projectList.length; i++) {
                        if (this.projectList[i].material.file_path === '') {
                            this.projectList[i].webProperties.materialThumbnail = '';
                        } else {
                            var _this = this;
                            this.projectList[i].webProperties.materialThumbnailShow = false;
                            var Result = await axios.get('/api/utilities/getFileReferenceUrlForThumbnail?file_path=' + this.projectList[i].material.file_path);
                            if (Result.data.status == 404) {
                                //声明自调用函数，没间隔2秒，向后端发送请求，当结果值为成功或者总请求数超过5次，就停止发送请求
                                (function autoIncrement(count, i) {
                                    //设置延迟函数，延迟2秒
                                    setTimeout(async function () {
                                        if (count < 4) {
                                            await axios.get('/api/utilities/getFileReferenceUrlForThumbnail?file_path=' + _this.projectList[i].material.file_path).then((res) => {
                                                if (res.data.status == 404) {
                                                    count++;
                                                    autoIncrement(count, i);   //接收到404后，执行自调用，再次发送请求
                                                } else {
                                                    _this.$set(_this.projectList[i].webProperties, 'materialThumbnail', res.data.url);
                                                    _this.projectList[i].webProperties.materialThumbnailShow = true;
                                                }
                                            })
                                        } else {
                                            _this.projectList[i].webProperties.materialThumbnailShow = true;
                                        }
                                    }, 2000);
                                })(0, i)
                            } else {
                                this.$set(this.projectList[i].webProperties, 'materialThumbnail', Result.data.url);
                                this.projectList[i].webProperties.materialThumbnailShow = true;
                            }
                        }
                    }
                } else {
                    this.$nextTick(async function () {
                        var _this = this;
                        this.projectList[itemVal].webProperties.materialThumbnailShow = false;
                        var Result = await axios.get('/api/utilities/getFileReferenceUrlForThumbnail?file_path=' + this.projectList[itemVal].material.file_path);
                        if (Result.data.status == 404) {
                            //声明自调用函数，没间隔2秒，向后端发送请求，当结果值为成功或者总请求数超过5次，就停止发送请求
                            (function autoIncrement(count, itemVal) {
                                //设置延迟函数，延迟2秒
                                setTimeout(async function () {
                                    if (count < 4) {
                                        await axios.get('/api/utilities/getFileReferenceUrlForThumbnail?file_path=' + _this.projectList[itemVal].material.file_path).then((res) => {
                                            if (res.data.status == 404) {
                                                count++;
                                                autoIncrement(count, itemVal);   //接收到404后，执行自调用，再次发送请求
                                            } else {
                                                _this.$set(_this.projectList[itemVal].webProperties, 'materialThumbnail', res.data.url);
                                                _this.projectList[itemVal].webProperties.materialThumbnailShow = true;
                                            }
                                        })
                                    } else {
                                        _this.projectList[itemVal].webProperties.materialThumbnailShow = true;
                                    }
                                }, 2000);
                            })(0, itemVal)
                        } else {
                            this.$set(this.projectList[itemVal].webProperties, 'materialThumbnail', Result.data.url);
                            this.projectList[itemVal].webProperties.materialThumbnailShow = true;
                        }
                    })
                }
            } catch (err) {
                console.log(err);
            }
        },
        //加载结果列表的缩略图
        async loadResultThumbnail(itemVal) {
            try {
                if (itemVal == null) {
                    for (var i = 0; i < this.projectList.length; i++) {
                        if (this.projectList[i].result_capture.file_path === '') {
                            this.projectList[i].webProperties.resultThumbnail = ''
                        } else {
                            var _this = this;
                            this.projectList[i].webProperties.resultThumbnailShow = false;
                            var Result = await axios.get('/api/utilities/getFileReferenceUrlForThumbnail?file_path=' + this.projectList[i].result_capture.file_path);
                            if (Result.data.status == 404) {
                                //声明自调用函数，没间隔2秒，向后端发送请求，当结果值为成功或者总请求数超过5次，就停止发送请求
                                (function autoIncrement(count, i) {
                                    //设置延迟函数，延迟2秒
                                    setTimeout(async function () {
                                        if (count < 4) {
                                            await axios.get('/api/utilities/getFileReferenceUrlForThumbnail?file_path=' + _this.projectList[i].result_capture.file_path).then((res) => {
                                                if (res.data.status == 404) {
                                                    count++;
                                                    autoIncrement(count, i);   //接收到404后，执行自调用，再次发送请求
                                                } else {
                                                    _this.$set(_this.projectList[i].webProperties, 'resultThumbnail', res.data.url);
                                                    _this.projectList[i].webProperties.resultThumbnailShow = true;
                                                }
                                            })
                                        } else {
                                            _this.projectList[i].webProperties.resultThumbnailShow = true;
                                        }
                                    }, 2000);
                                })(0, i)
                            } else {
                                this.$set(this.projectList[i].webProperties, 'resultThumbnail', Result.data.url);
                                this.projectList[i].webProperties.resultThumbnailShow = true;
                            }
                        }
                    }
                } else {
                    this.$nextTick(async function () {
                        var _this = this;
                        this.projectList[itemVal].webProperties.resultThumbnailShow = false;
                        var newThumbnail = await axios.get('/api/utilities/getFileReferenceUrlForThumbnail?file_path=' + this.projectList[itemVal].result_capture.file_path);
                        if (newThumbnail.data.status == 404) {
                            //声明自调用函数，没间隔2秒，向后端发送请求，当结果值为成功或者总请求数超过5次，就停止发送请求
                            (function autoIncrement(count, itemVal) {
                                //设置延迟函数，延迟2秒
                                setTimeout(async function () {
                                    if (count < 4) {
                                        await axios.get('/api/utilities/getFileReferenceUrlForThumbnail?file_path=' + _this.projectList[itemVal].result_capture.file_path).then((res) => {
                                            if (res.data.status == 404) {
                                                count++;
                                                autoIncrement(count, itemVal);   //接收到404后，执行自调用，再次发送请求
                                            } else {
                                                _this.$set(_this.projectList[itemVal].webProperties, 'resultThumbnail', res.data.url);
                                                _this.projectList[itemVal].webProperties.resultThumbnailShow = true;
                                            }
                                        })
                                    } else {
                                        _this.projectList[itemVal].webProperties.resultThumbnailShow = true;
                                    }
                                }, 2000);
                            })(0, itemVal)
                        } else {
                            this.$set(this.projectList[itemVal].webProperties, 'resultThumbnail', newThumbnail.data.url);
                            this.projectList[itemVal].webProperties.resultThumbnailShow = true;
                        }
                    })
                }
            } catch (err) {
                console.log(err);
            } finally {
                //用于解决列表排序后删除结果缩略图后，结果缩略图换位
                if (itemVal == null) {
                    for (var j = 0; j < this.projectList.length; j++) {
                        this.projectList[j].webProperties.resultThumbnailState = false;
                        this.projectList[j].webProperties.resultThumbnailState = true;
                    }
                }
            }
        },
        //根据【画像素材/結果キャプチャー】值，是否为空，判断遮罩是否显示
        shadeShowHid:function () {
            for (var i=0;i < this.projectList.length;i++){
                this.projectList[i].material.file_path == '' ? this.projectList[i].webProperties.materialShade = false : this.projectList[i].webProperties.materialShade = true;
                this.projectList[i].result_capture.file_path == '' ? this.projectList[i].webProperties.resultShade = false : this.projectList[i].webProperties.resultShade = true;
            }
        },
        //点击【全选按钮】进行全选与取消全选
        selectAll:function () {
            this.selectAllState = !this.selectAllState;
            if (this.selectAllState === true){
                for (var i=0;i < this.projectList.length;i++){
                    this.projectList[i].webProperties.projectCheck = true;
                }
            } else {
                for (var j=0;j < this.projectList.length;j++){
                    this.projectList[j].webProperties.projectCheck = false;
                }
            }
        },
        //点击【单选】如果【所有单选选中为真】则【全选状态为选中】否则【反之】
        select:function () {
            var check = document.getElementsByClassName('checkState');//获取到的是个数组
            var checkArr = [];
            this.projectList.forEach((item,index)=>{
                check[index].checked !== false ? item.webProperties.projectCheck = true : item.webProperties.projectCheck = false;
                checkArr.push(item.webProperties.projectCheck)
            });
            checkArr.indexOf(false) === -1 ? this.selectAllState = true : this.selectAllState = false;
        },
        //点击【添加按钮】新增一个项目
        addProject:function () {
            this.projectList.push(
                {
                    material: {
                        seq_no: '',
                        file_name: '',
                        file_path: '',
                        file_size: '',
                        display_size: '',
                        check_file_name: '',
                    },
                    result_capture: {
                        seq_no: '',
                        file_name: '',
                        file_path: '',
                        file_size: '',
                        display_size: '',
                    },
                    check: {
                        result: '',
                        abbey_id: '',
                        menu_name: '',
                        error_message: '',
                        result_comment: '',
                    },
                    webProperties: {
                        projectCheck: false,
                        resultShade: null,
                        materialShade: null,
                        indexVal:this.projectList.length,
                        resultThumbnailState:true,
                        resultThumbnailShow:true,
                        materialThumbnailShow:true,
                        materialThumbnail:'',
                        resultThumbnail:''
                    }
                }
            );
            this.$nextTick(function(){
                this.$refs.parentNode.$el.scrollTop = this.$refs.parentNode.$el.scrollHeight;
            });
        },
        //点击【删除按钮】，删除指定的项目
        dleProject:function () {
            for (var i = this.projectList.length-1;i>-1;i--){
                if (this.projectList[i].webProperties.projectCheck === true){
                    this.projectList.splice(i,1);
                }
            }
        },
        //点击【遮罩】弹出预览弹窗
        async previewPopup(index,differentiate) {
            try {
                this.$refs.previewPopup.dialog = true;
                this.$refs.previewPopup.fileFormatCode = null;
                this.$refs.previewPopup.fileSrc = null;
                this.loadingDisplay(true);
                let formData = new FormData();
                formData.append('step_id', this.initData['request_info']['step_id']);
                formData.append('request_id', this.initData['request_info']['request_id']);
                formData.append('request_work_id', this.initData['request_info']['id']);
                formData.append('task_id', this.initData['task_info']['id']);
                formData.append('file_path',differentiate == 'material' ? this.projectList[index].material.file_path : this.projectList[index].result_capture.file_path);
                var result = await  axios.post('/api/biz/b00007/s00013/downloadFile',formData);
                //把文件路径传给子组件
                this.$refs.previewPopup.fileSrc = result.data.src;
                this.$refs.previewPopup.countFileFormatCode(result.data.mime_type);
            } catch (err) {
                console.log(err);
            } finally {
                this.loadingDisplay(false)
            }
        },
        //点击【解压】弹出解压页面
        popupZip:function () {
            this.$refs.popupZip.dialog = true;
        },
        //点击【下载按钮】下载全部素材
        async downloadAllMaterial(){
            try {
                this.loadingDisplay(true);
                var url = '/api/biz/b00007/s00013/' + this.initData.request_info.id + '/' + this.initData.task_info.id + '/materialZipDownload';
                let formData = new FormData();
                formData.append('task_result_content', JSON.stringify(this.$parent.$parent.$parent.getAllComponentData()));
                await axios.post(url,formData).then((res)=>{
                    if (res.data.result.indexOf('success') != -1){
                        var blob = res.data.data.src;
                        var fileName = '素材全件ダウンロード.zip';
                        var elink = document.createElement('a');
                        elink.download = fileName;
                        elink.style.display = 'none';
                        elink.href = blob;
                        document.body.appendChild(elink);
                        elink.click();
                        URL.revokeObjectURL(elink.href); // 释放URL 对象
                        document.body.removeChild(elink)
                    } else {
                        this.$emit('errInformPopup',res.data.err_message);
                    }
                })
            } catch (err) {
                console.log(err);
            } finally {
                this.loadingDisplay(false)
            }
        },
        //点击【详细按钮】切换右侧模块
        switchModule:function (msg) {
            var data = {
                infoShow:false,
                projectInfo:msg
            };
            this.$emit('getMsg',data)  //向父级组件传递切换状态，控制【v-if】【v-else】
        },
        //在文件input内容变更时触发文件上传事件
        fileChange(el,item,differentiate,index) {
            if (!el.target.files[0].size) return;
            this.fileAdd(el.target.files[0],item,differentiate,index);
            el.target.value = ''
        },
        //验证上传文件的类型
        fileAdd(file,item,differentiate,index) {
            var _this = this;
            this.restrictFilesType(file);
            if (this.upload_state === true){
                let reader = new FileReader();
                reader.vue = this;
                reader.readAsDataURL(file);
                reader.onload = function () {
                    file.src = this.result;
                    _this.uploadFiles(file,item,differentiate,index);
                }
            } else {
                this.$emit('errInformPopup','拡張子が「mp4,jpeg,png,gif」のファイルのみアップできます。')
            }
        },
        //限制文件上传类型
        restrictFilesType(file){
            this.upload_state = null;
            var type = file.type.split('/')[1];
            this.restrict_file_type.indexOf(type) === -1 ? this.upload_state = false : this.upload_state = true;
        },
        //上传文件的接口
        async uploadFiles(files,item,differentiate,index){
            try {
                this.loadingDisplay(true);
                var _this = this;
                var url = '/api/biz/b00007/s00013/uploadFile';
                let formData = new FormData();
                formData.append('step_id', this.initData['request_info']['step_id']);
                formData.append('request_id', this.initData['request_info']['request_id']);
                formData.append('request_work_id', this.initData['request_info']['id']);
                formData.append('task_id', this.initData['task_info']['id']);
                formData.append('task_started_at', this.initData['task_started_at']);
                formData.append('task_result_content', JSON.stringify(this.$parent.$parent.$parent.getAllComponentData()));
                formData.append('file_name',files.name);
                formData.append('file_data',files.src);
                await axios.post(url,formData).then((res)=>{
                    if (res.data.result == 'success'){
                        item[differentiate] = res.data.data;
                        if (differentiate=='material'){
                            _this.loadMaterialThumbnail(index);
                        } else {
                            _this.loadResultThumbnail(index);
                        }
                    } else {
                        this.$emit('errInformPopup',JSON.stringify(res.data));
                    }
                })
            } catch (err) {
                console.log(err);
            } finally {
                this.loadingDisplay(false)
            }
        }
    }
}
</script>

<style scoped>
    @media (max-width: 1680px) {
        .project-list-box .project-list .judge{
            font-size: 16px !important;
        }
        .project-list-box .project-list-title td{
            font-size: 14px;
        }
    }
    @media (max-width: 1440px) {
        .project-list-box .project-list .judge{
            font-size: 14px !important;
        }
        .project-list-box .project-list-title td{
            font-size: 12px;
        }
    }
    .textRed{
        color: red !important;
        font-weight: bold;
    }
    .textGreen{
        color: #00D9AD !important;
        font-weight: bold;
    }
    .textGray{
        color: #7f7f7f !important;
        font-weight: bold;
    }
    /*清除浮动*/
    .project-list-box .project-list tr:last-child::after,
    .project-list-box .project-list tr::after,
    .project-list-box .project-list-title::after,
    .project-function-box::after{
        display: block;
        content: "";
        clear: both;
    }
    /*列表展开宽度*/
    .checkMore{
        width: 3% !important;
    }
    .materialMore,.resultMore{
        width: 16% !important;
    }
    .judgeMore{
        width: 8% !important;
    }
    .abbeyIdMore{
        width: 8% !important;
    }
    .MenuNameMore{
        width: 12% !important;
    }
    /*--*--*--*--*/
    .project-function-box{
        margin-bottom: 2px;
    }
    .project-function-box .operationProject{
        float: left;
        margin: 0 8px;
    }
    .project-function-box .operationProject:active path{
        fill: #444444;
    }
    .project-function-box #decompressionProject{
        transform: translateY(-3px);
    }
    .project-function-box #downloadAll{
        transform: translateY(2px);
    }
    .project-function-box .jump-box{
        height: 30px;
        float: right;
    }
    .project-function-box .jump-box .jump{
        font-size: 14px;
        color: #0BB3B7;
        line-height: 30px;
        text-decoration: none;
    }
    .project-function-box .jump-box .jump:hover{
        text-decoration: underline;
    }
    .project-function-box .jump-box .jump-icon{
        transform: translateY(2px);
    }
    .project-list-box{
        width: 100%;
    }
    .project-list-box .project-list-title{
        display: block;
        border: solid 1px #d7d7d7;
        line-height: 30px;
        padding: 8px 0;
        box-sizing: border-box;
        background-color: #fcfcfc;
    }
    .project-list-box .project-list-title td{
        display: block;
        float: left;
        border-right: solid 1px #C8C8C8;
        text-align: center;
        font-weight: bold;
        color: #555555;
    }
    .project-list-box .project-list-title td:last-child{
        border-right: none;
    }
    .project-list-box .project-list-title .check{
        height: 30px;
        line-height: 30px;
        cursor: auto;
        margin: 0;
    }
    .project-list-box .check{
        width: 6%;
        display: flex;
        height: 80px;
        line-height: 80px;
        margin: 10px 0;
        cursor: move;
    }
    .project-list-box .check::after,.project-list-box .judge::after{
        margin-top: 10px !important;
    }
    .project-list-box .check input{
        width:16px;
        height:16px;
        color: #7f7f7f;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
        line-height: 16px;
        margin: 0;
        position: relative;
    }
    .project-list-box .check input::before{
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #ffffff;
        border: 1px solid #aaaaaa;
    }
    .project-list-box .check input:checked::before{
        content: "\2714";
    }
    .project-list-box .check .checkAll{
        width: 18px;
        height: 18px;
        font-size: 18px;
        line-height: 18px;
    }
    .project-list-box .material,.project-list-box .result{
        width: 22%;
    }
    .project-list-box .judge{
        width: 13%;
    }
    .project-list-box .abbeyId{
        width: 13%;
    }
    .project-list-box .MenuName{
        width: 24%;
    }
    .project-list-box .errCenter,.project-list-box .inspectionResult{
        width: 18.5%;
    }
    .project-list-box .project-list{
        height: 333px;
        display: block;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .project-list-box .project-list::-webkit-scrollbar{
        display: none;
    }
    .project-list-box .project-list tr{
        display: block;
        border: solid 1px #d7d7d7;
        border-top: none;
        line-height: 30px;
        position: relative;
        box-sizing: border-box;
    }
    .project-list-box .project-list tr td{
        display: block;
        float: left;
        text-align: center;
        color: #555555;
    }
    .project-list-box .project-list tr td::after{
        display: block;
        height: 60px;
        content: "";
        margin: 20px 0;
        float: right;
        border-right: dotted 1px #C8C8C8;
    }
    .project-list-box .project-list tr td:nth-last-child(2)::after{
        display: none;
    }
    .project-list-box .project-list tr .Details{
        width: 30px;
        height: 100px;
        display: flex;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        background-color: #00D9AD;
        position: absolute;
        right: -31px;
        transition-duration:0.3s;
    }
    .project-list-box .project-list tr:hover .Details{
        transform: translateX(-31px);
    }
    .project-list-box .project-list .material,.project-list-box .project-list .result{
        position: relative;
        overflow: hidden;
        height: 100px;
    }
    .project-list-box .project-list .material:hover .info-box,.project-list-box .project-list .result:hover .info-box{
        transform: translateY(-100px);
    }
    .project-list-box .project-list .material .img-box,.project-list-box .project-list .result .img-box{
        width: 80%;
        height: 80px;
        margin: 10px auto 0 auto;
        display: inline-block;
        overflow: hidden;
    }
    .project-list-box .project-list .material .img-box img,
    .project-list-box .project-list .result .img-box img,
    .project-list-box .project-list .material .img-box video,
    .project-list-box .project-list .result .img-box video{
        height: 100%;
        max-width: 100%;
    }
    .project-list-box .project-list .info-box{
        width: 100%;
        height: 100px;
        padding: 0 5%;
        display: flex;
        flex-direction:column;
        justify-content:center;
        background-color: rgba(0,0,0,0.6);
        position: absolute;
        bottom: -100px;
        transition-duration:0.3s;
    }
    .project-list-box .project-list .info-box p{
        color: #ffffff;
        font-size: 14px;
        line-height: 20px;
        white-space:nowrap;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .project-list-box .project-list .file-box{
        background-position: center center;
        background-size: auto 60%;
        width: 100%;
        height: 100px;
        position: absolute;
        top: 0;
    }
    .project-list-box .project-list .matFile{
        background-image: url('/images/biz/b00007/project.svg');
    }
    .project-list-box .project-list .resFile{
        background-image: url('/images/biz/b00007/result.svg');
    }
    .project-list-box .project-list .file-box input{
        width: 100%;
        height: 100%;
        opacity: 0;
    }
    .project-list-box .project-list .judge{
        font-size: 18px;
        line-height: 80px;
        height: 80px;
        margin: 10px auto;
    }
    .project-list-box .project-list .errCenter,.project-list-box .project-list .inspectionResult,.project-list-box .project-list .abbeyId,.project-list-box .project-list .MenuName{
        height: 80px;
        margin: 10px 0;
        font-size: 14px;
        line-height: 20px;
        display: flex;
        align-items: center;
        text-align: left;
        overflow: hidden;
    }
    .project-list-box .project-list .MenuName,.project-list-box .project-list .abbeyId{
        text-align: center !important;
    }
    .project-list-box .project-list .errCenter span,.project-list-box .project-list .inspectionResult span,.project-list-box .project-list .abbeyId span,.project-list-box .project-list .MenuName span{
        width: 100%;
        padding:0 10%;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
    }
</style>
