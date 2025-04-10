<?php
namespace Unveels\TechPageApi\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class TechPageList implements ResolverInterface
{
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $data = [
            
                [
                    'tech_title_en' => 'LIMITLESS LUXURY & ADVANCED',
                    'tech_title_ar' => 'فخامة بلا حدود',
                    'tech_sub_title_en' => 'AI TECHNOLOGIES',
                    'tech_sub_title_ar' => 'تقنيات الذكاء الاصطناعي',
                    'tech_description_en' => 'At Unveels, we bring you a unique collection of cutting-edge technologies found nowhere else. Our commitment to excellence transforms your shopping experience, offering unmatched sophistication and innovation. Experience the future of luxury with us—where our exclusive technology sets us apart.',
                    'tech_description_ar' => 'نقدم لك مجموعة فريدة من التقنيات المتطورة التي لا توجد في أي مكان آخر. التزامنا بالتميز يغير تجربة التسوق الخاصة بك، ويقدم مستوى لا مثيل له من التطور والابتكار. اختبر مستقبل الرفاهية معنا - حيث تميزنا التكنولوجيا الحصرية.',
                    'tech_video' => 'https://vimeo.com/1067133685',
                    'tech_button_title_en' => 'Discover Unveels',
                    'tech_button_title_ar' => 'اكتشف أنڤيلز',
                    'tech_button_link' => ''
                ],
                [
                    'tech_title_en' => 'AI Makeup Try-On',
                    'tech_title_ar' => 'تجربة المكياج بالذكاء الإصطناعي',
                    'tech_sub_title_en' => 'Glamour at Your Fingertips',
                    'tech_sub_title_ar' => 'سِحر الجمال في مُتناول يدكِ!',
                    'tech_description_en' => 'Unveil the elegance of instant beauty transformation with our exclusive Virtual Makeup Try-On experience. From the sanctuary of your own home, explore a world of exquisite cosmetics and beauty essentials, tailored to your unique allure. Engage the cutting-edge live-camera technology to indulge in a real-time metamorphosis, as our sophisticated algorithm seamlessly applies your selected makeup treasures. Witness the art of beauty as it unfolds on your visage, empowering you to make a confident and informed choice, all while reveling in the luxury of personal style exploration.',
                    'tech_description_ar' => 'اكشفي عن سحر التحوّل الفوري مع تجربة المكياج بالذكاء الاصطناعي الحصرية التي نقدمها لكِ. من راحة منزلكِ، اكتشفِي عالمًا مِن مُستحضرات ومستلزمات التجميل الرائعة المُصممة خصيصًا لتُناسب جاذبيتك ومظهرك الفريد. باستخدامك الكاميرا الحية، ستستطيعين أن تجربي المكياج في نفس اللحظة ؛ حيث تقوم خوارزميات أنڤيلز المُتطورة بتمكينك من تطبيق المكياج الذي ترغبين في شرائه على وجهك بسهولة. شاهدي لمسات الجمال والأناقة وهي تتجلى على وجهك؛ لتتمكني مِن اتخاذ قرار الشراء بثقة.',
                    'tech_video' => 'https://vimeo.com/1067134204',
                    'tech_button_title_en' => 'TRY ON NOW',
                    'tech_button_title_ar' => 'جرب الان',
                    'tech_button_link' => 'makeupTryOn'
                ],
                [
                    'tech_title_en' => 'Find The Look',
                    'tech_title_ar' => 'البحث عن الإطلالة',
                    'tech_sub_title_en' => 'Curate Your Signature Style',
                    'tech_sub_title_ar' => 'افتح نافذة الأناقة على مصراعيها',
                    'tech_description_en' => 'Step into the realm of effortless elegance with our "Find The Look" feature, a gateway to unveiling the secrets of impeccable style. With a few seamless steps, immerse yourself in a world where inspiration knows no bounds. Use your Live Camera, Upload a photo or a video of any captivating look that catches your eye, and watch as our sophisticated technology uncovers the precise elements that compose its allure. Our innovative algorithms analyze the footage and reveal a curated collection of matching products, eliminating the mystery of style replication. Embrace the ease of discovery and empower your style with the essence of trendsetters, all while relishing the luxury of informed choices. Say farewell to uncertainty and embrace a world where fashion is an open book, personalized just for you.',
                    'tech_description_ar' => 'هي تقنية تُمكنك مِن كشف أسرار الأناقة الراقية. فقط مِن خلال بضع خطوات سهلة، ستنغمس في عالم لا حدود له من الالهام. قوم بتشغيل الكاميرا الحية أو حمل صورة أو فيديو للإطلالة التي تلفت انتباهك، وأطلق العنان لتلك التقنية لتكشف لكِ عن كُل المنتجات المستخدمة في تلك الإطلالة بأدق التفاصيل. ذلك لأنه تقوم خوارزمياتنا المبتكرة بالتحليل لتكشف لك عن مجموعة من المنتجات المطابقة لها، مما يزيل الغموض وراء كُل إطلالة تنال إعجابك. لتكون بذلك رمز للجمال والأناقة بين كافة الحضور وأينما كُنت. ودع حالة الشك وعانق عالمًا تكون فيه الموضة كتابًا مفتوحًا مخصصًا لك.',
                    'tech_video' => 'https://vimeo.com/1066966941',
                    'tech_button_title_en' => 'FIND A LOOK',
                    'tech_button_title_ar' => 'ابحث عن الإطلالة',
                    'tech_button_link' => 'findTheLookLive'
                ],
                [
                    'tech_title_en' => 'AI Skin Analysis',
                    'tech_title_ar' => 'تحليل البشرة بالذكاء الاصطناعي',
                    'tech_sub_title_en' => 'Reveal the Secrets of Radiant Skin',
                    'tech_sub_title_ar' => 'اكتشف أسرار وخبايا البشرة بسهولة!',
                    'tech_description_en' => 'Unlock the hidden potential of your complexion with our AI Skin Analysis feature, a revolutionary approach to personalized skincare. Harnessing the power of Unveels\' advanced AI Skin Analyzer, effortlessly assess the health of your face by simply using a live camera. This state-of-the-art technology meticulously detects a wide range of skin concerns, from spots and wrinkles to texture, dark circles, redness, oiliness, moisture levels, pores, eye bags, radiance, firmness, and more. With unparalleled precision, our analysis generates a bespoke regimen of product recommendations tailored to address each unique concern. Whether you seek a luxurious moisturizer, a targeted treatment, or a comprehensive skincare routine, our AI-powered analyzer ensures your skincare journey is not only effortless and efficient but exquisitely customized to fulfill your unique needs. Embrace the elegance of radiant skin and embark on a transformative path to beauty, where every detail is perfectly tailored to your individual essence.',
                    'tech_description_ar' => 'اكتشف المُشكلات الخفية لبشرتك مع تقنية تحليل وتقييم البشرة بالذكاء الاصطناعي التي تُعد طفرة حقيقة في عالم العناية بالبشرة. مِن خلال استخدام مُحلل بشرة ذكي قائم على الذكاء الاصطناعي؛ يُمكنك تقييم صحة وجهك دون عناء؛ بمُجرد استخدام الكاميرا الحية؛ ستكشف هذه التقنية المتطورة بدقة عن مجموعة من مشاكل البشرة التي قد تُعاني مِنها؛ بدءًا من البقع والتجاعيد إلى الملمس غير المُتجانس والهالات السوداء ومُشكلة التهيج والاحمرار ومشاكل البشرة الدهنية ومشاكل الترطيب والمسام وانتفاخ العينين والبشرة غير المُشرقة والترهل وغيرها. وبمنتهى الدقة، سيتم عرض كل المُنتجات التي تحل مشاكل بشرتك التي تمّ الكشف عنها؛ ليضمن بذلك لك المُحلل الرقمي رحلة موفقة ومضمونة النجاح مُصممة بشكل رائع لتلبية احتياجاتك الفريدة.',
                    'tech_video' => 'https://vimeo.com/1067133994',
                    'tech_button_title_en' => 'ANALYZE MY SKIN',
                    'tech_button_title_ar' => 'حلل البشرة',
                    'tech_button_link' => 'skinAnalysis'
                ],
                [
                    'tech_title_en' => 'AI Skin Tone Finder',
                    'tech_title_ar' => 'مكتشف لون البشرة بالذكاء الاصطناعي',
                    'tech_sub_title_en' => 'Illuminate Your True Hue',
                    'tech_sub_title_ar' => 'تألقي بألوانك',
                    'tech_description_en' => 'Discover the essence of your natural beauty with our AI Skin Tone Finder, a sophisticated tool designed to unveil your perfect palette. Effortlessly identify your skin tone with precision and grace, using our advanced technology to analyze subtle undertones and hues through a live camera. This personalized service empowers you to select the ideal foundation shades that harmonize with your unique complexion. Our AI Skin Tone Finder transforms your beauty experience, ensuring that every choice enhances your inherent radiance and complements your distinctive allure. Embrace the elegance of a tailored approach to cosmetics and skincare, where each selection is an exquisite reflection of your true self. Step into a world of color harmony, where your skin\'s natural beauty is celebrated and enhanced with unparalleled sophistication.',
                    'tech_description_ar' => 'اكتشفي جوهر جمالك الطبيعي باستخدام تقنية مكتشف لون البشرة. باستخدام الكاميرا الحية ستقوم هذه التقنية المتطورة بتحديد لون بشرتك ثم ستقوم الخوارزميات بعرض مجموعة من منتجات كريم الأساس التي تتناسب مع لون بشرتك بسهولة ودقة. تعمل هذه التقنية على تحويل تجربة جمالك، لتضمن أن كل خيار يعزز جمالك ويكمل جاذبيتك المميزة. استمتعي بأناقة مصممة خصيصًا لك. ادخلي إلى عالم من تناغم الألوان، حيث يتم الاحتفاء بجمال بشرتك الطبيعي وتعزيزه برقي لا مثيل له.',
                    'tech_video' => 'https://vimeo.com/1066979632',
                    'tech_button_title_en' => 'FIND MY SKIN TONE',
                    'tech_button_title_ar' => 'اكتشفي لون البشرة',
                    'tech_button_link' => 'skinToneFinder'
                ],
                [
                    'tech_title_en' => 'AI Face Analyzer',
                    'tech_title_ar' => 'مُحلل الوجه بالذكاء الاصطناعي',
                    'tech_sub_title_en' => 'Elegance Beyond Imagination',
                    'tech_sub_title_ar' => 'اكتشف جمال وجهك الحقيقي!',
                    'tech_description_en' => 'An extraordinary fusion of technology and elegance, designed to unlock the full potential of your natural beauty. Our advanced face analyzer meticulously studies your unique facial contours and attributes in real-time, offering curated products recommendations tailored exclusively to your features. Experience the art of precision with AI that understands the subtleties of your face, transforming each suggestion into a masterpiece. With AI Face Maestro, step into a world where every detail is elevated, every recommendation is impeccable, and your beauty is effortlessly refined.',
                    'tech_description_ar' => 'مزيج استثنائي من التكنولوجيا والأناقة، مصمم لإطلاق الإمكانات الكاملة لجمالك الطبيعي. يقوم محلل الوجه المتقدم الخاص بنا بدراسة ملامح وجهك وسماته الفريدة بدقة في الوقت الفعلي، ويقدم توصيات منسقة من الماكياج والإكسسوارات المصممة خصيصًا لتناسب ميزاتك. استمتع بتجربة فن الدقة مع الذكاء الاصطناعي الذي يفهم التفاصيل الدقيقة لوجهك، ويحول كل اقتراح إلى تحفة فنية. ادخل إلى عالم يتم فيه الارتقاء بكل التفاصيل وكل توصية، ويتم تعزيز جمالك دون عناء.',
                    'tech_video' => 'https://vimeo.com/1067134797',
                    'tech_button_title_en' => 'TRY ON NOW',
                    'tech_button_title_ar' => 'حلل الوجه',
                    'tech_button_link' => 'faceAnalyzer'
                ],
                [
                    'tech_title_en' => 'Shop The Look',
                    'tech_title_ar' => 'تسوق الإطلالة',
                    'tech_sub_title_en' => 'Elevate Your Style Instantly',
                    'tech_sub_title_ar' => 'لقد حان وقت إضافة لمسة مِن التجديد لأناقتك!',
                    'tech_description_en' => 'Step into a curated world of style with our "Shop The Look" feature, where elegance and convenience converge. Explore Unveels\' exclusive Look-book, a treasure trove of meticulously crafted looks designed to inspire and captivate. With a simple click, immerse yourself in the allure of each look, seamlessly trying on the essence of chic sophistication. Our intuitive hot spot feature allows you to effortlessly select and acquire any item within these curated collections. Transform your elegance with a single gesture and embrace the art of refined style at your fingertips. Experience the thrill of instant style elevation, where each look is a masterpiece waiting to be yours.',
                    'tech_description_ar' => 'انطلق إلى عالم من الأناقة المُنتقاة بعناية مع تقنية "تسوق الإطلالة" حيث تجتمع الأناقة والراحة والرفاهية. اكتشف كُتيب الإطلالات الحصري مِن أنڤيلز ؛ الذي يُعد كنز قيم يحتوي على إطلالات مُصممة بدقة متناهية لتلهمك وتأسرك. فقط بنقرة زر واحدة يُمكنك الانغماس في جاذبية كل إطلالة مِن خلال تجربة كل منتجات الإطلالة التي نالت إعجابك بسهولة. بنقرة زر واحدة يُمكنك اقتناء كُل ما تُريد دون أي جُهد؛ فهنا كل إطلالة هي تحفة فنية تنتظر أن تكون لك.',
                    'tech_video' => 'https://vimeo.com/1066974245',
                    'tech_button_title_en' => 'SHOP A LOOK',
                    'tech_button_title_ar' => 'تسوق الإطلالة',
                    'tech_button_link' => 'lookBookList'
                ],
                [
                    'tech_title_en' => 'AI Hand Accessories Try-On',
                    'tech_title_ar' => 'تجربة اكسسوارات اليد بالذكاء الاصطناعي',
                    'tech_sub_title_en' => 'A Smart Revolution in Hand Accessories Shopping',
                    'tech_sub_title_ar' => 'ثورة ذكية في عالم إكسسوارات اليد',
                    'tech_description_en' => 'Step into the future of personalized styling with AI Hand Try-On, the ultimate AI-powered experience that lets you virtually try on watches, rings, bracelets, and nail colors with unmatched precision and realism. With the power of artificial intelligence and augmented reality, both men and women can seamlessly explore different hand accessories in real-time. Simply activate the Live Camera to preview styles, experiment with designs, and make confident choices, without the need for physical trials. For those seeking a more refined beauty experience, the AI Nails Try-On technology offers an effortless way to try out a variety of nail colors and designs with exceptional accuracy, helping you find the perfect match before committing to a look. With AI Hand Accessories Try-On, every detail is designed to elevate your personal style, bringing convenience, precision, and innovation to the way you shop for hand accessories.',
                    'tech_description_ar' => 'اكتشف مستقبل التسوق الذكي مع هذه التقنية المتطورة التي تستخدم الذكاء الاصطناعي والواقع المعزز لتتيح لك تجربة الساعات، الخواتم، والأساور بدقة مذهلة وواقعية غير مسبوقة. بفضل هذه التكنولوجيا المتقدمة، يمكن للرجال والنساء استكشاف مختلف إكسسوارات اليد مباشرة عبر الكاميرا الحية، مما يسمح لهم بتجربة التصاميم المختلفة واتخاذ قرارات واثقة دون الحاجة إلى التجربة الفعلية. أما لمن يبحثن عن تجربة جمالية أكثر تطورا لأظافرهن ، فإن تقنية تجربة طلاء الأظافر بالواقع المعزز تتيح إمكانية استكشاف مجموعة واسعة من ألوان وتصاميم الأظافر بواقعية متناهية، مما يساعد على اختيار الإطلالة المثالية بكل ثقة وسهولة. مع تجربة اكسسوارات اليد بالذكاء الاصطناعي ، أصبح تسوق اكسسوارات اليد أكثر دقة وراحة.',
                    'tech_video' => 'https://vimeo.com/1066985026',
                    'tech_button_title_en' => 'TRY ON NOW',
                    'tech_button_title_ar' => 'جرب الان',
                    'tech_button_link' => ''
                ],
                [
                    'tech_title_en' => 'AI Accessories Try-On',
                    'tech_title_ar' => 'تجربة الإكسسوارات بالذكاء الاصطناعي',
                    'tech_sub_title_en' => 'Elegance Beyond Imagination',
                    'tech_sub_title_ar' => 'أناقة تفوق الخيال',
                    'tech_description_en' => 'Discover the art of accessorizing with unparalleled sophistication through our AI Accessories Try-On experience. Indulge in the allure of exquisite sunglasses, necklaces, hats, earrings, and more—all tailored to your personal style with just a click. This groundbreaking technology transforms your shopping experience, allowing you to visualize the perfect accessory ensemble with effortless grace. Say goodbye to uncertainty and embrace the confidence of a well-informed decision, all from the comfort of your home. Revel in the seamless fusion of fashion and innovation, crafted exclusively for the discerning shopper.',
                    'tech_description_ar' => 'اكتشفِ فن تنسيق الإكسسوارات بأناقة لا مثيل لها مِن خلال تجربة الإكسسوارات بالذكاء الاصطناعي المُقدمة لك مِن أنڤيلز. انغمسِ في جاذبية النظارات الشمسية والقلائد والقبعات والساعات وغيرها المصممة لتناسب أسلوبك الشخصي كل ذلك فقط بنقرة واحدة. تعمل هذه التقنية الرائدة على إعادة تعريف رحلة التسوق الاعتيادية؛ فهي تسمح لك بتجربة أي قطعة اكسسوار مُتردد في شرائها قبل إتخاذ قرار الشراء الفعلي؛ كل ذلك وأنت في منزلك ومِن وراء شاشة جهازك. فأهلاً بك في عالم الموضة الذي لا حدود له.',
                    'tech_video' => 'https://vimeo.com/1066983573',
                    'tech_button_title_en' => 'TRY ON NOW',
                    'tech_button_title_ar' => 'جرب الان',
                    'tech_button_link' => 'accessoriesTryOn'
                ],
                [
                    'tech_title_en' => 'AI Skin Simulation',
                    'tech_title_ar' => 'مُحاكاة البشرة بالذكاء الاصطناعي',
                    'tech_sub_title_en' => 'Envision Your Future Radiance',
                    'tech_sub_title_ar' => 'تصور شكلك المستقبلي',
                    'tech_description_en' => 'Step into the realm of possibilities with our AI Skin Simulation feature, where you can glimpse the future of your complexion with unparalleled accuracy. Harness the power of cutting-edge technology to predict and visualize the transformative effects of beauty products on your skin. Simply use the live camera to initiate a seamless analysis that reveals potential improvements tailored to your unique skin profile. This innovative technology provides a detailed simulation of how specific products can enhance your skin\'s vitality and radiance, offering valuable insights into your skincare journey. Embrace the luxury of informed decisions and the confidence of knowing exactly how your skin will flourish. With our AI Skin Simulation, you are empowered to make choices that reflect your aspirations for timeless beauty and luminous allure.',
                    'tech_description_ar' => 'ادخل إلى عالم لا نهاية له من الاحتمالات من خلال تقنية محاكاة البشرة المدعومة بالذكاء الاصطناعي، حيث يمكنك إلقاء نظرة خاطفة على مستقبل بشرتك بدقة لا مثيل لها. استغل قوة التكنولوجيا المتطورة للتنبؤ وتصور التأثيرات التحويلية لمنتجات التجميل على بشرتك. ما عليك سوى استخدام الكاميرا الحية ألبدء تحليل سلس يكشف عن التحسينات المحتملة المصممة خصيصًا لملف تعريف بشرتك الفريد. توفر هذه التقنية المبتكرة محاكاة تفصيلية لكيفية عمل منتجات معينة وتحسين بشرتك وإشراقها، مما يوفر رؤية قيمة في رحلة العناية بالبشرة. استمتع برفاهية القرارات الواثقة و معرفة كيف ستزدهر بشرتك بالضبط. من خلال تقتية محاكاة البشرة المدعمة بالذكاء الاصطناعي، يمكنك اتخاذ الخيارات التي تعكس تطلعاتك إلى الجمال الخالد والجاذبية الأخاذة.',
                    'tech_video' => 'https://vimeo.com/1067132787',
                    'tech_button_title_en' => 'SIMULATE MY SKIN',
                    'tech_button_title_ar' => 'حاكى بشرتي',
                    'tech_button_link' => 'seeImprovement'
                ],
                [
                    'tech_title_en' => 'AI Personality Finder',
                    'tech_title_ar' => 'مُحلل الشخصية  بالذكاء الاصطناعي',
                    'tech_sub_title_en' => 'Discover Your Essence, Define Your Style',
                    'tech_sub_title_ar' => 'اكتشف شخصيتك، حدد أسلوبك!',
                    'tech_description_en' => 'Step into a world where your individuality is celebrated with our AI Personality Finder feature. Through a sophisticated analysis of your facial attributes—such as the contour of your nose and the shape of your lips—our advanced technology unveils the nuances of your personality in real-time via live camera interactions. This cutting-edge AI experience transcends traditional shopping by offering you a curated selection of products that resonate with your unique essence. In a few seamless steps, receive bespoke recommendations that harmonize with your personal style and preferences, ensuring that every choice is a reflection of your true self. Embrace the luxury of a personalized shopping experience, where each suggestion is crafted with precision and care, transforming the way you discover and indulge in the perfect products for you.',
                    'tech_description_ar' => 'أهلاً بك في عالم يتم فيه اكتشاف شخصيتك وبُناءً عليه يتم اقتراح كُل ماهو مُناسب لك وفقًا لشخصيتك باستخدام الذكاء الاصطناعي. مِن خلال تحليل متطور لملامح وجهك كالأنف وشكل شفتيك ستكشف هذه التقنية المتطورة عن السمات الدقيقة لشخصيتك وذلك فقط مِن خلال استخدامك للكاميرا الحية. هذه التقنية تُمكنك مِن كسر حدود رحلة التسوق التقليدية، لأنها تقترح عليك المنتجات المُناسبة لك تمامًا دون عناء. فقد حان وقت الاستمتاع برحلة تسوق مختلفة عن أي وقتٍ مضى.',
                    'tech_video' => 'https://vimeo.com/1067134597',
                    'tech_button_title_en' => 'FIND MY PERSONALITY',
                    'tech_button_title_ar' => 'اعثر على شخصيتي',
                    'tech_button_link' => 'personalityFinder'
                ],
                [
                    'tech_title_en' => 'AI Smart Beauty Mirror',
                    'tech_title_ar' => 'مرآة الجمال بالذكاء الاصطناعي',
                    'tech_sub_title_en' => 'Reflections of Elegance, The Ultimate Smart Mirror Experience',
                    'tech_sub_title_ar' => 'انعكاسات الأناقة، تجربة المرآة الذكية المطلقة',
                    'tech_description_en' => 'Transform your beauty routine with our state-of-the-art smart mirror. Effortlessly try different shades of makeup using simple voice commands. Experience the perfect blend of luxury and innovation, as the smart mirror responds intuitively to your desires, elevating your beauty regimen to new heights of sophistication.',
                    'tech_description_ar' => 'غيّري روتين جمالك باستخدام مرآتنا الذكية المتطورة. يمكنك تطبيق ألوان مختلفة من المكياج بسهولة باستخدام أوامر صوتية بسيطة. استمتعي بالمزيج المثالي من الفخامة والابتكار، حيث تستجيب المرآة الذكية لرغباتك بشكل تلقائي، مما يرفع روتين جمالك إلى آفاق جديدة من التطور.',
                    'tech_video' => 'https://vimeo.com/1067133203',
                    'tech_button_title_en' => 'Use Smart Mirror',
                    'tech_button_title_ar' => 'جربي الان',
                    'tech_button_link' => 'smartBeauty'
                ],
                [
                    'tech_title_en' => 'AR Home Accessories',
                    'tech_title_ar' => 'الواقع الإفتراضي المُعزز للإكسسوارات المنزلية',
                    'tech_sub_title_en' => 'Transform Your Space with Visionary Precision',
                    'tech_sub_title_ar' => 'اضف لمسة مِن التجديد لبيتك بدقة خيالية!',
                    'tech_description_en' => 'Experience the future of home décor with our Augmented Reality for Home Accessories feature, where imagination meets reality in the most exquisite way. Elevate your interior design journey by effortlessly visualizing your chosen pieces within your living spaces before making a purchase. Our cutting-edge AR technology offers an immersive and interactive experience, allowing you to place and view home accessories in your real-world environment with stunning accuracy. No longer worry about whether an item will harmonize with your décor; instead, delight in the luxury of informed decisions that ensure every piece fits seamlessly into your home. Embrace the confidence of transformative design at your fingertips and redefine your space with elegance and ease.',
                    'tech_description_ar' => 'اختبر رحلة تنسيق اكسسوارات المنزل بنظرة مُستقبلية مع تقنية الواقع الإفتراضي المُعزز للإكسسوارات المنزلية؛ حيث يلتقي الخيال مع الواقع بأروع وأبسط الطرق. ارتقِ برحلتك في التصميم الداخلي من خلال تصوّر القطع التي اخترتها داخل مساحات المعيشة الخاصة بك دون عناء وقبل اتخاذ قرار الشراء. تقدم لك هذه التقنية تجربة تفاعلية تتيح لك وضع الإكسسوارات المنزلية وعرضها في بيئتك الفعلي بدقة مُذهلة باستخدام الكاميرا الحية. لا قلق بعد الآن بشأن ما إذا كانت قطعة ما ستتناغم مع ديكور منزلك أم لا، بل تمتع برفاهية الشراء.',
                    'tech_video' => 'https://vimeo.com/1066971510',
                    'tech_button_title_en' => 'Try Now',
                    'tech_button_title_ar' => 'جرب الآن',
                    'tech_button_link' => 'category/924'
                ]
            
        ];
        return $data;
    }
}