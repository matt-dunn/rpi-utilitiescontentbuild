<?php

namespace RPI\Utilities\ContentBuild\Processors;

class Sprites implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const VERSION = "1.0.7";
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;
    
    const MAX_SPRITE_WIDTH = 1024;
    const SPRITE_PADDING = 2;
    
    protected $maxSpriteWidth = self::MAX_SPRITE_WIDTH;

    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->project = $project;
        
        if (isset($options, $options["maxSpriteWidth"])) {
            $this->maxSpriteWidth = $options["maxSpriteWidth"];
            if (!is_numeric($this->maxSpriteWidth) || $this->maxSpriteWidth < 0 || $this->maxSpriteWidth > 10000) {
                throw new \Exception(__CLASS__.": maxSpriteWidth must be a integer between 0 and 10000");
            }
        }
    }
    
    public static function getVersion()
    {
        return "v".self::VERSION;
    }
    
    public function init(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        $processorIndex
    ) {
        $sprites = $processor->getMetadata("sprites");
        if (isset($sprites) && $sprites !== false) {
            foreach ($sprites as $sprite) {
                if (file_exists($sprite["spriteName"])) {
                    unlink($sprite["spriteName"]);
                }
                if (file_exists($sprite["spriteDebugName"])) {
                    unlink($sprite["spriteDebugName"]);
                }
            }
        }

        $processor->setMetadata("sprites", null);
        
        \RPI\Foundation\Event\Manager::addEventListener(
            "RPI\Utilities\ContentBuild\Events\ImageCheckAvailability",
            function (\RPI\Foundation\Event $event, $params) use ($processor) {
                $sprites = $processor->getMetaData("sprites");
                foreach ($sprites as $sprite) {
                    if ($sprite["spritePath"] == $params["imageUri"]) {
                        $event->srcEvent->setReturnValue(true);
                        break;
                    }
                }
            }
        );
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $outputFilename,
        $buffer
    ) {
        $spriteOutputFilename = dirname($outputFilename)."/I/Sprites/".$build->name.".png";
        $debugSpriteOutputFilename = null;
        if (isset($build->debugPath)) {
            $debugSpriteOutputFilename = $build->debugPath."/I/Sprites/".$build->name.".png";
        }
        
        $sprites = $processor->getMetaData("sprites");
        if ($sprites === false) {
            $sprites = array();
        }
        
        $spritePadding = self::SPRITE_PADDING;
        $maxSpriteWidth = $this->maxSpriteWidth;
        $project = $this->project;
        
        preg_replace_callback(
            "/(#sprite\:\s*url\s*\(\s*'*\"*(.*?)'*\"*\s*\)\s*;)/sim",
            function ($matches) use (
                $inputFilename,
                $outputFilename,
                $build,
                &$sprites,
                $spriteOutputFilename,
                $debugSpriteOutputFilename,
                $maxSpriteWidth,
                $resolver,
                $project,
                $spritePadding
                ) {
                $spriteFilename = $resolver->realpath($project, $matches[2]);
                if ($spriteFilename === false) {
                    $spriteFilename = realpath(dirname($inputFilename)."/".$matches[2]);
                }
                if (!file_exists($spriteFilename)) {
                    $project->getLogger()->error(
                        "Unable to locate sprite image '{$matches[2]}' in '$inputFilename'"
                    );
                } else {
                    if (!isset($sprites[$spriteFilename])) {
                        $project->getLogger()->debug(
                            "Creating Sprite image ' $spriteFilename'"
                        );
                        $imageDataSprite = getimagesize($spriteFilename);

                        $im = null;
                        $offsetX = 0;
                        $offsetY = 0;

                        if (file_exists($spriteOutputFilename)) {
                            $previousSprite =
                                \RPI\Utilities\ContentBuild\Processors\Sprites::findLastIcon($build, $sprites);
                            if ($previousSprite !== false) {
                                $offsetX = $previousSprite["offsetX"] + $previousSprite["width"] + $spritePadding;
                                $offsetY = $previousSprite["offsetY"];
                            }

                            $imageDataSpriteOutput = getimagesize($spriteOutputFilename);

                            $outputSpriteWidth = max($imageDataSpriteOutput[0], $offsetX + $imageDataSprite[0]);
                            $outputSpriteHeight = max($imageDataSpriteOutput[1], $offsetY + $imageDataSprite[1]);

                            if ($outputSpriteWidth > $maxSpriteWidth) {
                                $outputSpriteWidth = $imageDataSpriteOutput[0];
                                $outputSpriteHeight = $imageDataSpriteOutput[1]
                                    + $imageDataSprite[1]
                                    + $spritePadding;
                                $offsetX = 0;
                                $offsetY = $imageDataSpriteOutput[1] + $spritePadding;
                            }

                            $imOriginal = imagecreatefrompng($spriteOutputFilename);
                            imagealphablending($imOriginal, false);
                            imagesavealpha($imOriginal, true);

                            $im = imagecreatetruecolor($outputSpriteWidth + $spritePadding, $outputSpriteHeight);
                            imagealphablending($im, false);
                            imagesavealpha($im, true);
                            $alpha = imagecolorallocatealpha($im, 0, 0, 0, 127);
                            imagefill($im, 0, 0, $alpha);

                            imagecopy(
                                $im,
                                $imOriginal,
                                0,
                                0,
                                0,
                                0,
                                $imageDataSpriteOutput[0],
                                $imageDataSpriteOutput[1]
                            );

                            imagedestroy($imOriginal);
                        } else {
                            $im = imagecreatetruecolor($imageDataSprite[0], $imageDataSprite[1]);
                            imagealphablending($im, false);
                            imagesavealpha($im, true);
                            $alpha = imagecolorallocatealpha($im, 0, 0, 0, 127);
                            imagefill($im, 0, 0, $alpha);
                        }

                        if (isset($im)) {
                            $type = explode("/", $imageDataSprite["mime"]);
                            $type = $type[1];
                            $imageCreateFunction = "imagecreatefrom".$type;

                            $im2 = $imageCreateFunction($spriteFilename);
                            imagealphablending($im2, false);
                            imagesavealpha($im2, true);
                            imagecopy($im, $im2, $offsetX, $offsetY, 0, 0, $imageDataSprite[0], $imageDataSprite[1]);

                            if (!file_exists(dirname($spriteOutputFilename))) {
                                $oldumask = umask(0);
                                mkdir(dirname($spriteOutputFilename), 0755, true);
                                umask($oldumask);
                            }
                
                            imagepng($im, $spriteOutputFilename);
                            
                            if (isset($debugSpriteOutputFilename)) {
                                if (!file_exists(dirname($debugSpriteOutputFilename))) {
                                    $oldumask = umask(0);
                                    mkdir(dirname($debugSpriteOutputFilename), 0755, true);
                                    umask($oldumask);
                                }

                                copy($spriteOutputFilename, $debugSpriteOutputFilename);
                            }

                            imagedestroy($im);
                            imagedestroy($im2);
                        } else {
                            $project->getLogger()->error(
                                "Unable to create image ' $spriteFilename'"
                            );
                        }

                        $sprites[$spriteFilename] = array(
                            "width" => $imageDataSprite[0],
                            "height" => $imageDataSprite[1],
                            "offsetX" => $offsetX,
                            "offsetY" => $offsetY,
                            "buildName" => $build->name,
                            "spriteName" => $spriteOutputFilename,
                            "spriteDebugName" => $debugSpriteOutputFilename,
                            "spritePath" => substr($spriteOutputFilename, strlen(dirname($outputFilename)) + 1),
                            "originalName" => $spriteFilename
                        );
                    }
                }
            },
            $buffer
        );
            
        $processor->setMetadata("sprites", $sprites);
        
        return $buffer;
    }
    
    public function process(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        $inputFilename,
        $buffer
    ) {
        $sprites = $processor->getMetaData("sprites");
        if (isset($sprites)) {
            $project = $this->project;
            $buffer = preg_replace_callback(
                "/(#sprite\:\s*url\s*\(\s*'*\"*(.*?)'*\"*\s*\)\s*;)/sim",
                function ($matches) use ($inputFilename, $sprites, $project, $resolver) {
                    $spriteImage = $resolver->realpath($project, $matches[2]);
                    if ($spriteImage === false) {
                        $spriteImage = realpath(dirname($inputFilename)."/".$matches[2]);
                    }
                    if (isset($sprites, $sprites[$spriteImage])) {
                        $spriteData = $sprites[$spriteImage];

                        $offsetX = $spriteData["offsetX"];
                        if ($offsetX > 0) {
                            $offsetX = $offsetX * -1;
                        }
                        $offsetY = $spriteData["offsetY"];
                        if ($offsetY > 0) {
                            $offsetY = $offsetY * -1;
                        }

                        // This needs to be on a single line so that line number reporting
                        // does not break (e.g. when using firebug)
                        return "background:url({$spriteData["spritePath"]}) no-repeat {$offsetX}px {$offsetY}px;".
                            "width:{$spriteData["width"]}px;height:{$spriteData["height"]}px;content:'';";
                    } else {
                        $project->getLogger()->error(
                            "Unable to locate sprite image '{$matches[2]}' in '$inputFilename'"
                        );
                    }

                    return "";
                },
                $buffer
            );
        }
        
        return $buffer;
    }
    
    public function complete(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor
    ) {
        
    }
    
    public function canProcessBuffer()
    {
        return true;
    }
    
    public static function findLastIcon($build, array $sprites)
    {
        $sprites = array_reverse($sprites);
        foreach ($sprites as $sprite) {
            if (isset($sprite["buildName"]) && $sprite["buildName"] == $build->name) {
                return $sprite;
            }
        }
        
        return false;
    }
}
