<?php


/**
 * Масив с думи, които ще се прескачат
 */
$stopWordsArr = array(
        'about' => 'about',
        'from' => 'from',
        'that' => 'that',
        'this' => 'this',
        'what' => 'what',
        'when' => 'when',
        'where' => 'where',
        'will' => 'will',
        'with' => 'with',
        'able' => 'able',
        'above' => 'above',
        'according' => 'according',
        'accordingly' => 'accordingly',
        'across' => 'across',
        'actually' => 'actually',
        'after' => 'after',
        'afterwards' => 'afterwards',
        'again' => 'again',
        'against' => 'against',
        'allow' => 'allow',
        'allows' => 'allows',
        'almost' => 'almost',
        'alone' => 'alone',
        'along' => 'along',
        'already' => 'already',
        'also' => 'also',
        'although' => 'although',
        'always' => 'always',
        'among' => 'among',
        'amongst' => 'amongst',
        'another' => 'another',
        'anybody' => 'anybody',
        'anyhow' => 'anyhow',
        'anyone' => 'anyone',
        'anything' => 'anything',
        'anyway' => 'anyway',
        'anyways' => 'anyways',
        'anywhere' => 'anywhere',
        'apart' => 'apart',
        'appear' => 'appear',
        'appreciate' => 'appreciate',
        'appropriate' => 'appropriate',
        'aren' => 'aren',
        'around' => 'around',
        'aside' => 'aside',
        'asking' => 'asking',
        'associated' => 'associated',
        'available' => 'available',
        'away' => 'away',
        'awfully' => 'awfully',
        'became' => 'became',
        'because' => 'because',
        'become' => 'become',
        'becomes' => 'becomes',
        'becoming' => 'becoming',
        'been' => 'been',
        'before' => 'before',
        'beforehand' => 'beforehand',
        'behind' => 'behind',
        'being' => 'being',
        'believe' => 'believe',
        'below' => 'below',
        'beside' => 'beside',
        'besides' => 'besides',
        'best' => 'best',
        'better' => 'better',
        'between' => 'between',
        'beyond' => 'beyond',
        'both' => 'both',
        'brief' => 'brief',
        'came' => 'came',
        'cannot' => 'cannot',
        'cant' => 'cant',
        'cause' => 'cause',
        'causes' => 'causes',
        'certain' => 'certain',
        'certainly' => 'certainly',
        'changes' => 'changes',
        'clearly' => 'clearly',
        'come' => 'come',
        'comes' => 'comes',
        'concerning' => 'concerning',
        'consequently' => 'consequently',
        'consider' => 'consider',
        'considering' => 'considering',
        'contain' => 'contain',
        'containing' => 'containing',
        'contains' => 'contains',
        'corresponding' => 'corresponding',
        'could' => 'could',
        'couldn' => 'couldn',
        'course' => 'course',
        'currently' => 'currently',
        'definitely' => 'definitely',
        'described' => 'described',
        'despite' => 'despite',
        'didn' => 'didn',
        'different' => 'different',
        'does' => 'does',
        'doesn' => 'doesn',
        'doing' => 'doing',
        'done' => 'done',
        'down' => 'down',
        'downwards' => 'downwards',
        'during' => 'during',
        'each' => 'each',
        'eight' => 'eight',
        'either' => 'either',
        'else' => 'else',
        'elsewhere' => 'elsewhere',
        'enough' => 'enough',
        'entirely' => 'entirely',
        'especially' => 'especially',
        'even' => 'even',
        'ever' => 'ever',
        'every' => 'every',
        'everybody' => 'everybody',
        'everyone' => 'everyone',
        'everything' => 'everything',
        'everywhere' => 'everywhere',
        'exactly' => 'exactly',
        'example' => 'example',
        'except' => 'except',
        'fifth' => 'fifth',
        'first' => 'first',
        'five' => 'five',
        'followed' => 'followed',
        'following' => 'following',
        'follows' => 'follows',
        'former' => 'former',
        'formerly' => 'formerly',
        'forth' => 'forth',
        'four' => 'four',
        'further' => 'further',
        'furthermore' => 'furthermore',
        'gets' => 'gets',
        'getting' => 'getting',
        'given' => 'given',
        'gives' => 'gives',
        'goes' => 'goes',
        'going' => 'going',
        'gone' => 'gone',
        'gotten' => 'gotten',
        'greetings' => 'greetings',
        'hadn' => 'hadn',
        'happens' => 'happens',
        'hardly' => 'hardly',
        'hasn' => 'hasn',
        'have' => 'have',
        'haven' => 'haven',
        'having' => 'having',
        'hello' => 'hello',
        'help' => 'help',
        'hence' => 'hence',
        'here' => 'here',
        'hereafter' => 'hereafter',
        'hereby' => 'hereby',
        'herein' => 'herein',
        'hereupon' => 'hereupon',
        'hers' => 'hers',
        'herself' => 'herself',
        'himself' => 'himself',
        'hither' => 'hither',
        'hopefully' => 'hopefully',
        'howbeit' => 'howbeit',
        'however' => 'however',
        'ignored' => 'ignored',
        'immediate' => 'immediate',
        'inasmuch' => 'inasmuch',
        'indeed' => 'indeed',
        'indicate' => 'indicate',
        'indicated' => 'indicated',
        'indicates' => 'indicates',
        'inner' => 'inner',
        'insofar' => 'insofar',
        'instead' => 'instead',
        'into' => 'into',
        'inward' => 'inward',
        'itself' => 'itself',
        'just' => 'just',
        'keep' => 'keep',
        'keeps' => 'keeps',
        'kept' => 'kept',
        'know' => 'know',
        'known' => 'known',
        'knows' => 'knows',
        'last' => 'last',
        'lately' => 'lately',
        'later' => 'later',
        'latter' => 'latter',
        'latterly' => 'latterly',
        'least' => 'least',
        'less' => 'less',
        'lest' => 'lest',
        'like' => 'like',
        'liked' => 'liked',
        'likely' => 'likely',
        'little' => 'little',
        'look' => 'look',
        'looking' => 'looking',
        'looks' => 'looks',
        'mainly' => 'mainly',
        'many' => 'many',
        'maybe' => 'maybe',
        'mean' => 'mean',
        'meanwhile' => 'meanwhile',
        'merely' => 'merely',
        'might' => 'might',
        'more' => 'more',
        'moreover' => 'moreover',
        'most' => 'most',
        'mostly' => 'mostly',
        'much' => 'much',
        'must' => 'must',
        'myself' => 'myself',
        'name' => 'name',
        'namely' => 'namely',
        'near' => 'near',
        'nearly' => 'nearly',
        'necessary' => 'necessary',
        'need' => 'need',
        'needs' => 'needs',
        'neither' => 'neither',
        'never' => 'never',
        'nevertheless' => 'nevertheless',
        'next' => 'next',
        'nine' => 'nine',
        'nobody' => 'nobody',
        'none' => 'none',
        'noone' => 'noone',
        'normally' => 'normally',
        'nothing' => 'nothing',
        'novel' => 'novel',
        'nowhere' => 'nowhere',
        'obviously' => 'obviously',
        'often' => 'often',
        'okay' => 'okay',
        'once' => 'once',
        'ones' => 'ones',
        'only' => 'only',
        'onto' => 'onto',
        'other' => 'other',
        'others' => 'others',
        'otherwise' => 'otherwise',
        'ought' => 'ought',
        'ours' => 'ours',
        'ourselves' => 'ourselves',
        'outside' => 'outside',
        'over' => 'over',
        'overall' => 'overall',
        'particular' => 'particular',
        'particularly' => 'particularly',
        'perhaps' => 'perhaps',
        'placed' => 'placed',
        'please' => 'please',
        'plus' => 'plus',
        'possible' => 'possible',
        'presumably' => 'presumably',
        'probably' => 'probably',
        'provides' => 'provides',
        'quite' => 'quite',
        'rather' => 'rather',
        'really' => 'really',
        'reasonably' => 'reasonably',
        'regarding' => 'regarding',
        'regardless' => 'regardless',
        'regards' => 'regards',
        'relatively' => 'relatively',
        'respectively' => 'respectively',
        'right' => 'right',
        'said' => 'said',
        'same' => 'same',
        'saying' => 'saying',
        'says' => 'says',
        'second' => 'second',
        'secondly' => 'secondly',
        'seeing' => 'seeing',
        'seem' => 'seem',
        'seemed' => 'seemed',
        'seeming' => 'seeming',
        'seems' => 'seems',
        'seen' => 'seen',
        'self' => 'self',
        'selves' => 'selves',
        'sensible' => 'sensible',
        'sent' => 'sent',
        'serious' => 'serious',
        'seriously' => 'seriously',
        'seven' => 'seven',
        'several' => 'several',
        'shall' => 'shall',
        'should' => 'should',
        'shouldn' => 'shouldn',
        'since' => 'since',
        'some' => 'some',
        'somebody' => 'somebody',
        'somehow' => 'somehow',
        'someone' => 'someone',
        'something' => 'something',
        'sometime' => 'sometime',
        'sometimes' => 'sometimes',
        'somewhat' => 'somewhat',
        'somewhere' => 'somewhere',
        'soon' => 'soon',
        'sorry' => 'sorry',
        'specified' => 'specified',
        'specify' => 'specify',
        'specifying' => 'specifying',
        'still' => 'still',
        'such' => 'such',
        'sure' => 'sure',
        'take' => 'take',
        'taken' => 'taken',
        'tell' => 'tell',
        'tends' => 'tends',
        'than' => 'than',
        'thank' => 'thank',
        'thanks' => 'thanks',
        'thanx' => 'thanx',
        'thats' => 'thats',
        'their' => 'their',
        'theirs' => 'theirs',
        'them' => 'them',
        'themselves' => 'themselves',
        'then' => 'then',
        'thence' => 'thence',
        'there' => 'there',
        'thereafter' => 'thereafter',
        'thereby' => 'thereby',
        'therefore' => 'therefore',
        'therein' => 'therein',
        'theres' => 'theres',
        'thereupon' => 'thereupon',
        'these' => 'these',
        'they' => 'they',
        'think' => 'think',
        'third' => 'third',
        'thorough' => 'thorough',
        'thoroughly' => 'thoroughly',
        'those' => 'those',
        'though' => 'though',
        'three' => 'three',
        'through' => 'through',
        'throughout' => 'throughout',
        'thru' => 'thru',
        'thus' => 'thus',
        'together' => 'together',
        'took' => 'took',
        'toward' => 'toward',
        'towards' => 'towards',
        'tried' => 'tried',
        'tries' => 'tries',
        'truly' => 'truly',
        'trying' => 'trying',
        'twice' => 'twice',
        'under' => 'under',
        'unfortunately' => 'unfortunately',
        'unless' => 'unless',
        'unlikely' => 'unlikely',
        'until' => 'until',
        'unto' => 'unto',
        'upon' => 'upon',
        'used' => 'used',
        'useful' => 'useful',
        'uses' => 'uses',
        'using' => 'using',
        'usually' => 'usually',
        'value' => 'value',
        'various' => 'various',
        'very' => 'very',
        'want' => 'want',
        'wants' => 'wants',
        'wasn' => 'wasn',
        'welcome' => 'welcome',
        'well' => 'well',
        'went' => 'went',
        'were' => 'were',
        'weren' => 'weren',
        'whatever' => 'whatever',
        'whence' => 'whence',
        'whenever' => 'whenever',
        'whereafter' => 'whereafter',
        'whereas' => 'whereas',
        'whereby' => 'whereby',
        'wherein' => 'wherein',
        'whereupon' => 'whereupon',
        'wherever' => 'wherever',
        'whether' => 'whether',
        'which' => 'which',
        'while' => 'while',
        'whither' => 'whither',
        'whoever' => 'whoever',
        'whole' => 'whole',
        'whom' => 'whom',
        'whose' => 'whose',
        'willing' => 'willing',
        'wish' => 'wish',
        'within' => 'within',
        'without' => 'without',
        'wonder' => 'wonder',
        'would' => 'would',
        'wouldn' => 'wouldn',
        'your' => 'your',
        'yours' => 'yours',
        'yourself' => 'yourself',
        'yourselves' => 'yourselves',
        'zero' => 'zero'
);
