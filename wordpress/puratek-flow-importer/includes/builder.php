<?php
if (!defined('ABSPATH')) { exit; }

/** Native mode-5 data, based on an exported FunnelKit 3.8.5.2 block document. */
final class PFI_Builder {
    private int $serial = 0;
    private array $counts = [];
    private DOMDocument $dom;

    public static function html(string $html): void {
        PFI_Package::check(strlen($html) > 0 && strlen($html) <= 104448, 'Email HTML must contain 1–102 KB.');
        PFI_Package::check(!preg_match('~<\s*(script|iframe|object|embed|form)\b|\bon[a-z]+\s*=|(?:javascript|vbscript)\s*:|data\s*:\s*text/html~i', $html), 'Unsafe active email content.');
    }

    public static function email(array $email): void {
        PFI_Package::check(is_string($email['subject'] ?? null) && trim($email['subject']) !== '' && strlen($email['subject']) <= 998, 'Email subject is missing or too long.');
        PFI_Package::check(in_array($email['mode'] ?? null, [1,3,4,5], true) && is_array($email['data'] ?? null) && is_string($email['template'] ?? null), 'Invalid native email format.');
        self::html($email['template']);
        if (($email['mode'] ?? 0) === 5) {
            $block = $email['data']['block'] ?? null;
            PFI_Package::check(is_array($block) && is_string($block['body'] ?? null) && strlen($block['body']) <= 524288 && is_array($block['settings'] ?? null) && is_string($block['template'] ?? null), 'Visual Builder email requires body, settings and rendered template.');
            PFI_Package::check(str_contains($block['body'], '<!-- wp:email-block/'), 'Missing native editable email blocks.');
            self::html($block['template']);
            PFI_Package::check($block['template'] === $email['template'], 'Builder rendered template and email template differ. Save/export the email again.');
        }
    }

    public static function convert(array $email): array {
        self::html((string)($email['template'] ?? ''));
        if (($email['mode'] ?? 0) === 5) { self::email($email); return $email; }
        PFI_Package::check(class_exists('DOMDocument'), 'PHP DOM is required for Visual Builder conversion.');
        $converter = new self();
        $converter->dom = new DOMDocument('1.0','UTF-8');
        $before = libxml_use_internal_errors(true);
        try {
            PFI_Package::check($converter->dom->loadHTML('<?xml encoding="UTF-8">'.$email['template'], LIBXML_NONET), 'Cannot parse email HTML.');
        } finally { libxml_clear_errors(); libxml_use_internal_errors($before); }
        $xpath = new DOMXPath($converter->dom);
        $container = $xpath->query('//table[@data-email]')->item(0) ?? $xpath->query('//body')->item(0);
        PFI_Package::check($container instanceof DOMElement, 'Email body is missing.');
        $body = $converter->walk($container);
        PFI_Package::check($body !== '', 'No supported editable email content found.');
        $settings = ['background'=>['desktop'=>['color'=>'#F2F2F2']], 'contentBackground'=>['desktop'=>['color'=>'#FFFFFF']], 'width'=>['desktop'=>['value'=>600,'unit'=>'px']], 'align'=>['desktop'=>'center']];
        $email['mode'] = 5;
        $email['data'] = is_array($email['data'] ?? null) ? $email['data'] : [];
        $email['data']['block'] = ['body'=>$body,'settings'=>$settings,'template'=>$email['template']];
        $email['data']['pfi_conversion'] = ['schema'=>1,'blocks'=>$converter->counts,'source_hash'=>hash('sha256',$email['template']),'visual_edit_verified'=>false];
        self::email($email);
        return $email;
    }

    private function block(string $type, array $attrs, string $inner = ''): string {
        $attrs = ['uniqueID'=>substr(hash('sha256', 'pfi-block-'.(++$this->serial)),0,7)] + $attrs;
        $this->counts[$type] = ($this->counts[$type] ?? 0) + 1;
        // Gutenberg attribute escaping prevents HTML/comment delimiters corrupting the document.
        $json = str_replace(['--','<','>','&'], ['\\u002d\\u002d','\\u003c','\\u003e','\\u0026'], wp_json_encode($attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $inner === '' ? '<!-- wp:email-block/'.$type.' '.$json.' /-->' . "\n" : '<!-- wp:email-block/'.$type.' '.$json.' -->' . "\n".$inner.'<!-- /wp:email-block/'.$type.' -->' . "\n";
    }

    private function css(DOMElement $node): array {
        $styles = [];
        for ($at=$node; $at instanceof DOMElement; $at=$at->parentNode) {
            $parts = explode(';', $at->getAttribute('style'));
            foreach ($parts as $part) {
                $pair = explode(':',$part,2);
                if (count($pair)===2) { $key=strtolower(trim($pair[0])); if (!isset($styles[$key])) { $styles[$key]=trim($pair[1]); } }
            }
        }
        return $styles;
    }

    private function attrs(DOMElement $node): array {
        $css = $this->css($node);
        $font=['family'=>$css['font-family'] ?? 'Arial, Helvetica, sans-serif'];
        if (isset($css['font-size'])) { $font['size']=(float)$css['font-size']; }
        return ['font'=>['desktop'=>$font,'mobile'=>(object)[]], 'isHidden'=>['desktop'=>false,'mobile'=>false], 'color'=>['desktop'=>$css['color']??'#454545'], 'text'=>['desktop'=>['align'=>$css['text-align']??'left']], 'conditional'=>['rules'=>[],'data'=>'']];
    }

    private function wrap(string $inner, DOMElement $node): string {
        $css=$this->css($node);
        $column=$this->block('column',['width'=>['desktop'=>['width'=>'600','unit'=>'px']]],$inner);
        $section=$this->block('section',['layout'=>['desktop'=>'column'],'columnsWidth'=>[100],'contentBackground'=>['desktop'=>['color'=>$css['background-color']??'#FFFFFF']], 'padding'=>['desktop'=>['top'=>'8','right'=>'32','bottom'=>'8','left'=>'32','unit'=>'px']]],$column);
        return $this->block('row',[], $section);
    }

    private function walk(DOMNode $node): string {
        if (!$node instanceof DOMElement) { return ''; }
        $tag=strtolower($node->tagName);
        if (in_array($tag,['head','style','script','title','meta'],true) || str_contains($node->getAttribute('style'),'display:none')) { return ''; }
        // A cart product table becomes one native dynamic widget, not static sample rows.
        if ($tag==='table' && str_contains($this->dom->saveHTML($node),'PFI_RUNTIME_CART_ROWS')) {
            $hasNested=false;
            foreach($node->getElementsByTagName('table') as $child) { if(str_contains($this->dom->saveHTML($child),'PFI_RUNTIME_CART_ROWS')) {$hasNested=true;break;} }
            if(!$hasNested) {
                $attrs=$this->attrs($node)+['enabledContentOptions'=>['desktop'=>['image','title','attrs','qty','price']],'alignment'=>['desktop'=>'center'],'productTitleBold'=>['desktop'=>true]];
                return $this->wrap($this->block('cart-items',$attrs),$node);
            }
        }
        if ($tag==='img') {
            $attrs=$this->attrs($node)+['image'=>['url'=>$node->getAttribute('src'),'alt'=>$node->getAttribute('alt')], 'autoWidth'=>['desktop'=>false,'mobile'=>false], 'imageWidth'=>['desktop'=>(int)($node->getAttribute('width')?:536),'mobile'=>0], 'width'=>['desktop'=>['width'=>'100','unit'=>'%']],'widthUnit'=>['desktop'=>'%']];
            return $this->wrap($this->block('image',$attrs),$node);
        }
        if ($tag==='a' && $node->getElementsByTagName('img')->length>0) {
            $image=$node->getElementsByTagName('img')->item(0);
            $attrs=$this->attrs($image)+['image'=>['url'=>$image->getAttribute('src'),'alt'=>$image->getAttribute('alt')], 'url'=>$node->getAttribute('href'),'target'=>'_blank','autoWidth'=>['desktop'=>false,'mobile'=>false], 'width'=>['desktop'=>['width'=>'100','unit'=>'%']]];
            return $this->wrap($this->block('image',$attrs),$image);
        }
        if (in_array($tag,['p','h1','h2','h3','h4','h5','h6','a','ul','ol','blockquote'],true)) {
            $content=$this->dom->saveHTML($node);
            if(trim($node->textContent)==='') { return ''; }
            if($tag==='a' && str_contains($node->getAttribute('href'),'PFI_RUNTIME_CART_URL')) {
                $attrs=$this->attrs($node)+['content'=>trim($node->textContent),'bgColor'=>['desktop'=>'#F7931E'],'autoWidth'=>['desktop'=>false],'widthPercent'=>['desktop'=>['value'=>100]],'padding'=>['desktop'=>['top'=>'16','right'=>'20','bottom'=>'16','left'=>'20','unit'=>'px']]];
                return $this->wrap($this->block('cart-link',$attrs),$node);
            }
            if(str_contains($content,'PFI_RUNTIME_COUPON')) {
                return $this->wrap($this->block('coupon',$this->attrs($node)+['code'=>'PFI_RUNTIME_COUPON','enabledContentOptions'=>['desktop'=>['code']],'codeAutoWidth'=>['desktop'=>true]]),$node);
            }
            // Styled anchors remain editable native Text blocks; no unverified button schema is invented.
            return $this->wrap($this->block('text',$this->attrs($node)+['content'=>$content]),$node);
        }
        $out='';
        foreach($node->childNodes as $child) {
            if($child instanceof DOMText && trim($child->textContent)!=='') {
                $text=htmlspecialchars($child->textContent,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
                $out.=$this->wrap($this->block('text',$this->attrs($node)+['content'=>$text]),$node);
            } else { $out.=$this->walk($child); }
        }
        return $out;
    }

    /** Native editor's visible email overrides only the variant currently loaded there. */
    public static function selected(array $sidebar, string $variant): ?array {
        $variants=$sidebar['pfi_variants']??[];
        if(($sidebar['pfi_editor_variant']??'')===$variant && is_array($sidebar['bwfan_email_data']??null)) { return $sidebar['bwfan_email_data']; }
        return $variants[$variant]??null;
    }

    public static function package(array $package): array {
        foreach($package['flows'] as &$flow) {
            foreach($flow['payload']['step_data'] as &$step) {
                $d=PFI_Package::json($step['data']);
                if(empty($d['sidebarData']['pfi_variants'])) { continue; }
                foreach($d['sidebarData']['pfi_variants'] as &$email) { $email=self::convert($email); } unset($email);
                $key=array_key_first($d['sidebarData']['pfi_variants']);
                $d['sidebarData']['pfi_editor_variant']=$key;
                $d['sidebarData']['bwfan_email_data']=$d['sidebarData']['pfi_variants'][$key];
                $step['data']=wp_json_encode($d);
            } unset($step);
            $flow['version']='2.0.0-builder';
            $flow['payload']['meta']['pfi_builder']=1;
            PFI_Package::validate_workflow($flow['payload']);
        } unset($flow);
        return $package;
    }
}
