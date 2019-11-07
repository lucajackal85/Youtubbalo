<?php


namespace Jackal\Youtubbalo\Command;

use Google_Client;
use Google_Service_YouTube;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CredentialCommand extends Command
{
    private $oauth2Filename = 'php-yt-oauth2.json';

    protected function configure()
    {
        $this->setName('jackal:youtubbalo:create-credential')
            ->addArgument('client-secret', InputArgument::REQUIRED)
            ->addArgument('oauth2', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $credentialsPath = $input->getArgument('client-secret');

        if (!file_exists($credentialsPath)) {
            throw new \RuntimeException('File '.$credentialsPath.' not found');
        }

        $oauth2Path = null;
        if ($input->getArgument('oauth2')) {
            $oauth2Path = $input->getArgument('oauth2').$this->oauth2Filename;
        }

        $client = new Google_Client();

        $client->setAuthConfig($credentialsPath);
        // Set to valid redirect URI for your project.
        $client->setRedirectUri('http://localhost');
        $client->addScope([Google_Service_YouTube::YOUTUBE, Google_Service_YouTube::YOUTUBE_UPLOAD]);
        $client->setAccessType('offline');

        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        $output->writeln(sprintf("Open the following link in your browser:\n%s\n", $authUrl));
        $output->writeln('Enter verification code:');
        $authCode = trim(fgets(STDIN));
        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        if ($oauth2Path) {
            file_put_contents($oauth2Path, json_encode($accessToken));
            $output->writeln("Credentials saved to %s\n", realpath($oauth2Path));
        } else {
            $output->writeln('######################################################');
            $output->writeln(json_encode($accessToken));
            $output->writeln('######################################################');
        }
    }
}
