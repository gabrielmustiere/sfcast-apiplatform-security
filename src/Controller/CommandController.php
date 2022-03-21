<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class CommandController extends AbstractController
{
    #[Route('/command', name: 'command')]
    public function index(KernelInterface $kernel): Response
    {
        $app = new Application($kernel);
        $app->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'c:c',
        ]);

        $output = new BufferedOutput();
        $app->run($input, $output);

        $content = $output->fetch();

        return $this->render('command/index.html.twig', [
            'output' => $content,
        ]);
    }

}
