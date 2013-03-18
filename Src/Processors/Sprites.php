<?php

namespace RPI\Utilities\ContentBuild\Processors;

/**
 * TODO: check #sprite:url(...); syntax
 */

class Sprites implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const MAX_SPRITE_WIDTH = 1024;
    const SPRITE_PADDING = 2;
    const VERSION = "1.0.5";
    
    private $maxSpriteWidth = self::MAX_SPRITE_WIDTH;

    public function __construct($options = null)
    {
        if (isset($options, $options["maxSpriteWidth"])) {
            $this->maxSpriteWidth = $options["maxSpriteWidth"];
            if (!is_numeric($this->maxSpriteWidth) || $this->maxSpriteWidth < 0 || $this->maxSpriteWidth > 10000) {
                throw new \Exception(__CLASS__.": maxSpriteWidth must be a integer between 0 and 10000");
            }
        }
    }
    
    public function getVersion()
    {
        return "v".self::VERSION;
    }
    
    public function init(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
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
        
        \RPI\Utilities\ContentBuild\Event\Manager::addEventListener(
            "RPI\Utilities\ContentBuild\Events\ImageCheckAvailability",
            function (\RPI\Utilities\ContentBuild\Event $event, $params) use ($processor) {
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
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
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
        
        $maxSpriteWidth = $this->maxSpriteWidth;
        
        preg_replace_callback(
            "/(#sprite\:\s*url\((.*?)\)\s*;)/sim",
            function ($matches) use (
                $inputFilename,
                $outputFilename,
                $build,
                &$sprites,
                $spriteOutputFilename,
                $debugSpriteOutputFilename,
                $maxSpriteWidth
                ) {
                if (!file_exists(dirname($inputFilename)."/".$matches[2])) {
                    \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                        "Unable to locate sprite image '{$matches[2]}' in '$inputFilename'",
                        LOG_ERR
                    );
                } else {
                    $spriteFilename = realpath(dirname($inputFilename)."/".$matches[2]);
                    
                    if (!isset($sprites[$spriteFilename])) {
                        \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                            "Creating Sprite image ' $spriteFilename'",
                            LOG_DEBUG
                        );
                        $imageDataSprite = getimagesize($spriteFilename);

                        $im = null;
                        $offsetX = 0;
                        $offsetY = 0;

                        if (file_exists($spriteOutputFilename)) {
                            $previousSprite =
                                \RPI\Utilities\ContentBuild\Processors\Sprites::findLastIcon($build, $sprites);
                            if ($previousSprite !== false) {
                                $offsetX = $previousSprite["offsetX"] + $previousSprite["width"] + self::SPRITE_PADDING;
                                $offsetY = $previousSprite["offsetY"];
                            }

                            $imageDataSpriteOutput = getimagesize($spriteOutputFilename);

                            $outputSpriteWidth = max($imageDataSpriteOutput[0], $offsetX + $imageDataSprite[0]);
                            $outputSpriteHeight = max($imageDataSpriteOutput[1], $offsetY + $imageDataSprite[1]);

                            if ($outputSpriteWidth > $maxSpriteWidth) {
                                $outputSpriteWidth = $imageDataSpriteOutput[0];
                                $outputSpriteHeight = $imageDataSpriteOutput[1]
                                    + $imageDataSprite[1]
                                    + self::SPRITE_PADDING;
                                $offsetX = 0;
                                $offsetY = $imageDataSpriteOutput[1] + self::SPRITE_PADDING;
                            }

                            $imOriginal = imagecreatefrompng($spriteOutputFilename);
                            imagealphablending($imOriginal, false);
                            imagesavealpha($imOriginal, true);

                            $im = imagecreatetruecolor($outputSpriteWidth, $outputSpriteHeight);
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
                            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                                "Unable to create image ' $spriteFilename'",
                                LOG_ERR
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
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $inputFilename,
        $buffer
    ) {
        $sprites = $processor->getMetaData("sprites");
        if (isset($sprites)) {
            $buffer = preg_replace_callback(
                "/(#sprite\:\s*url\((.*?)\)\s*;)/sim",
                function ($matches) use ($inputFilename, $sprites) {
                    $spriteImage = realpath(dirname($inputFilename)."/".$matches[2]);
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
                        \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                            "Unable to locate sprite image '{$matches[2]}' in '$inputFilename'",
                            LOG_ERR
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
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
    ) {
        
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
