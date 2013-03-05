<?php

namespace RPI\Utilities\ContentBuild\Processors;

class Sprites implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const MAX_SPRITE_WIDTH = 1024;
    
    public function getOptions()
    {
        return array(
            
        );
    }

    public function init(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
    ) {
        $processor->setMetadata("sprites", null);
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $outputFilename,
        $buffer
    ) {
        $debugPath = $build->debugPath;
        $spriteOutputFilename = dirname($outputFilename)."/I/Sprites/".$build->name.".png";
        $debugSpriteOutputFilename = $debugPath."/I/Sprites/".$build->name.".png";
        
        $sprites = $processor->getMetaData("sprites");
        if ($sprites === false) {
            $sprites = array();
        }
        
        $maxSpriteWidth = self::MAX_SPRITE_WIDTH;
        
        preg_replace_callback(
            "/^\s*(#sprite\:\s*(.*?);)/sim",
            function ($matches) use ($inputFilename, $outputFilename, $build, &$sprites, $spriteOutputFilename, $debugSpriteOutputFilename, $maxSpriteWidth) {
                if (!file_exists(dirname($inputFilename)."/".$matches[2])) {
                    \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Unable to locate image '{$matches[2]}' in '$inputFilename'", LOG_ERR);
                } else {
                    $spriteFilename = realpath(dirname($inputFilename)."/".$matches[2]);
                    
                    if (!isset($sprites[$spriteFilename])) {
                        \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Creating Sprite image ' $spriteFilename'", LOG_DEBUG);
                        $imageDataSprite = getimagesize($spriteFilename);

                        $im = null;
                        $offsetX = 0;
                        $offsetY = 0;

                        if (file_exists($spriteOutputFilename)) {
                            $previousSprite = \RPI\Utilities\ContentBuild\Processors\Sprites::findLastIcon($build, $sprites);
                            if ($previousSprite !== false) {
                                $offsetX = $previousSprite["offsetX"] + $previousSprite["width"];
                                $offsetY = $previousSprite["offsetY"];
                            }

                            $imageDataSpriteOutput = getimagesize($spriteOutputFilename);

                            $outputSpriteWidth = max($imageDataSpriteOutput[0], $offsetX + $imageDataSprite[0]);
                            $outputSpriteHeight = max($imageDataSpriteOutput[1], $offsetY + $imageDataSprite[1]);

                            if ($outputSpriteWidth > $maxSpriteWidth) {
                                $outputSpriteWidth = $imageDataSpriteOutput[0];
                                $outputSpriteHeight = $imageDataSpriteOutput[1] + $imageDataSprite[1];
                                $offsetX = 0;
                                $offsetY = $imageDataSpriteOutput[1];
                            }

                            $imOriginal = imagecreatefrompng($spriteOutputFilename);
                            imagealphablending($imOriginal, false);
                            imagesavealpha($imOriginal, true);

                            $im = imagecreatetruecolor($outputSpriteWidth, $outputSpriteHeight);
                            imagealphablending($im, false);
                            imagesavealpha($im, true);
                            $alpha = imagecolorallocatealpha($im, 0, 0, 0, 127);
                            imagefill($im, 0, 0, $alpha);

                            imagecopy($im, $imOriginal, 0, 0, 0, 0, $imageDataSpriteOutput[0], $imageDataSpriteOutput[1]);

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
                
                            if (!file_exists(dirname($debugSpriteOutputFilename))) {
                                $oldumask = umask(0);
                                mkdir(dirname($debugSpriteOutputFilename), 0755, true);
                                umask($oldumask);
                            }
                            
                            imagepng($im, $spriteOutputFilename);
                            copy($spriteOutputFilename, $debugSpriteOutputFilename);

                            imagedestroy($im);
                            imagedestroy($im2);
                        } else {
                            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Unable to create image ' $spriteFilename'", LOG_ERR);
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
//                            "relativePath" => \RPI\Utilities\Build\Content\Build::makeRelativePath(dirname($spriteFilename), dirname($spriteOutputFilename))."/".basename($spriteFilename)
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
        $inputFilename,
        $buffer
    ) {
        $sprites = $processor->getMetaData("sprites");
        if (isset($sprites)) {
            $buffer = preg_replace_callback(
                "/(#sprite\:\s*(.*?);)/sim",
                function ($matches) use ($inputFilename, $sprites) {
                    $spriteImage = realpath(dirname($inputFilename)."/".$matches[2]);
                    $spriteData = null;
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

                        $spriteDetails = "";
//                        if ($runtime) {
//                            $spriteDetails = "/*! Sprite: {$spriteData["originalName"]} */";
//                        }
                        // This needs to be on a single line so that line number reporting does not break (e.g. when using firebug)
                        return <<<EOT
{$spriteDetails} background:url({$spriteData["spritePath"]}) no-repeat {$offsetX}px {$offsetY}px;width:{$spriteData["width"]}px;height:{$spriteData["height"]}px;content:'';
EOT;
                    }

                    return "";
                    return "SPRITE:".  dirname($inputFilename)." - ".print_r($spriteData, true);

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
