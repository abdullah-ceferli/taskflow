🚀 *TASKFLOW – İŞ PROSESİ*

*1️⃣ Laravel layihəsinin hazırlanması*

🔹 Boş bir *Laravel* layihəsi yaradın.
🔹 Layihənin adı *TaskFlow* olsun.
🔹 Sizə göndərdiyim ZIP faylındakı *sample* folderini TaskFlow layihəsinin *root* hissəsinə əlavə edin.

---

*2️⃣ Codex ilə development*

🤖 Daha sonra *Codex* ilə layihəni açın, *TaskFlow* layihəsini seçin və aşağıdakı promptu olduğu kimi göndərin:

📌 *CODEX PROMPTU*

```text
TaskFlow layihəsi üzərində işləyəcəyik.

Əvvəlcə aşağıdakı məlumatları və qaydaları diqqətlə oxu və layihə daxilində qeyd et ki, development boyunca yadda qalsın.

KONTEKST

sample folder daxilində 2 layihə var.

Biz həmin layihələri əvvəlcədən analiz etmişik və analiz nəticələrini:

sample/project-analysis.md

faylına yazmışıq.

Daha sonra hər iki layihədə nələrin yaxşı olduğunu və məqsədimizə çatmaq üçün əlavə olaraq nələrin edilməli olduğunu müəyyən etmişik.

Bunun əsasında:

sample/taskflow.md

və analiz nəticələrindən istifadə edərək:

sample/implementation-plan.md

hazırlamışıq.


ƏSAS TAPŞIRIQ

Sən sample/implementation-plan.md faylındakı planı tətbiq etməlisən.

Ancaq:

sample/roadmap.md

faylına QƏTİ ŞƏKİLDƏ toxunmamalısan.

Roadmap yalnız implementation-plan.md tam şəkildə bitdikdən sonra icra ediləcək.

Bu dəyişməz qaydadır:

1. Əvvəlcə implementation-plan.md tam bitməlidir.
2. Implementation plan bitdikdən sonra mənə report verməlisən.
3. Roadmap-a keçməzdən əvvəl məndən açıq şəkildə icazə istəməlisən.
4. Mən icazə vermədən roadmap.md daxilindəki heç bir taskı icra etməməlisən.


AGENTS.MD

sample folder daxilində olan layihələrdən hər hansı birindəki AGENTS.md faylını yeni TaskFlow layihəsinin root hissəsinə əlavə et.

Hər iki sample layihəsində bu fayl eynidir.


LAYİHƏ ÜÇÜN YADDA SAXLANILACAQ MƏLUMATLAR

Burada yazdığım məlumatların bir hissəsi layihə haqqında kontekstdir, digər hissəsi isə development qaydalarıdır.

Bunları yeni layihə daxilində uyğun şəkildə qeyd et ki, sonrakı mərhələlərdə də bu məlumatlar və qaydalar qorunsun.


NWIDART QURAŞDIRILMASI

Yeni TaskFlow layihəsi boş Laravel layihəsidir.

İlk texniki addım olaraq Nwidart Laravel Modules paketini layihəyə sıfırdan quraşdır və düzgün konfiqurasiya et.

sample folder daxilindəki layihələrdə Nwidart artıq quraşdırılıb.

Ancaq yeni TaskFlow layihəsi sıfırdan yaradıldığı üçün onların hazır konfiqurasiyasını kor-koranə kopyalama.

Yeni layihədə Nwidart-ı düzgün şəkildə sıfırdan qur.


İŞ PROSESİ

Development-i mənimlə birlikdə addım-addım aparacaqsan.

Taskları böyük şəkildə etmə.

implementation-plan.md daxilindəki işi mümkün qədər kiçik və məntiqli tasklara böl.

HƏR TASK ÜÇÜN BU PROSES MƏCBURİDİR:

1. Taska başlamazdan əvvəl mənə izah et:
   - indi hansı taskı edəcəyik;
   - nəyi dəyişəcəyik;
   - bunu niyə edirik;
   - nəticədə nə əldə olunacaq.

2. Daha sonra həmin taskı icra et.

3. Task bitdikdən sonra mənə report ver:
   - hansı fayllar yaradıldı;
   - hansı fayllar dəyişdirildi;
   - konkret olaraq nə edildi;
   - varsa hansı command-lar işlədildi;
   - nəticə necə yoxlanıldı;
   - növbəti task nə olacaq.

4. Yalnız bundan sonra növbəti taska keç.


MƏNİM İŞTİRAKIM TƏLƏB OLUNARSA

Əgər hər hansı mərhələdə mənim tərəfimdən:

- qərar verilməsi,
- məlumat təqdim edilməsi,
- credential/API key verilməsi,
- seçim edilməsi,
- manual əməliyyat aparılması,
- əlavə fayl təqdim edilməsi

və ya başqa hər hansı müdaxilə tələb olunarsa:

özün fərziyyə edib davam etmə.

Məndən konkret olaraq nə lazım olduğunu istə və yalnız həmin məsələ həll olunduqdan sonra davam et.


ƏSAS QAYDALARIN XÜLASƏSİ

- implementation-plan.md əsas icra planıdır.
- roadmap.md faylına toxunmaq olmaz.
- implementation-plan.md bitmədən roadmap icra edilə bilməz.
- roadmap-a keçmək üçün əvvəlcə məndən icazə alınmalıdır.
- AGENTS.md yeni layihənin root hissəsinə əlavə edilməlidir.
- Nwidart yeni Laravel layihəsinə sıfırdan qurulmalıdır.
- Tasklar kiçik hissələrə bölünməlidir.
- Hər taskdan ƏVVƏL izah verilməlidir.
- Hər taskdan SONRA report verilməlidir.
- Mənim iştirakım lazım olan məsələlərdə əvvəlcə məndən məlumat/əməliyyat istənilməlidir.
- Mənim icazəm olmadan roadmap mərhələsinə keçmək qadağandır.

İndi əvvəlcə mövcud faylları və sənədləri analiz et, qaydaları layihə daxilində qeyd et və ilk kiçik taskın nə olacağını mənə izah et.

İlk taskı izah etmədən icraya başlama.
```

---

*3️⃣ Paralel praktika layihəsi*

💻 Siz isə paralel olaraq əlavə bir *boş Laravel layihəsi* yaradın və orada da *Nwidart* quraşdırın.

Codex hər bir taskı bitirdikdən sonra sizə:

🔹 nə etdiyini,
🔹 niyə etdiyini,
🔹 hansı faylları dəyişdirdiyini,
🔹 hansı kodları əlavə etdiyini

izah edəcək.

Siz də həmin taskı öz *ikinci boş Laravel layihənizdə* tətbiq edəcəksiniz.

💡 Burada iki üsuldan istifadə edə bilərsiniz:

👉 *1-ci üsul – Tövsiyə olunan:*
Əvvəlcə Codex-in izahına baxaraq taskı özünüz etməyə çalışın. Daha sonra Codex-in etdiyi implementation ilə müqayisə edib səhvlərinizi və fərqləri yoxlayın.

👉 *2-ci üsul – Daha rahat variant:*
Əgər task sizə çətin gəlsə, Codex-in yazdığı kodu və etdiyi dəyişiklikləri analiz edib eyni şeyi ikinci layihədə özünüz təkrar edin.

---

🎯 *Məqsədimiz*

Burada əsas məqsəd sadəcə layihəni hazır şəkildə əldə etmək deyil.

*Codex bizə izah edəcək, biz isə paralel şəkildə praktika edəcəyik.*

Beləliklə həm real layihə development-i görəcəyik, həm də eyni məntiqi özümüz tətbiq edərək öyrənəcəyik. 👨‍💻🧠

🤝 *Bir-birinizlə daim kontaktda qalın.*

Problem yaranarsa bir-birinizə kömək edin.
Mənə sualınız olarsa, istənilən vaxt soruşun. ✅
