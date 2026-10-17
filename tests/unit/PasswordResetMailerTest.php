<?php

use App\Services\Auth\PasswordResetMailer;
use CodeIgniter\Test\CIUnitTestCase;

final class PasswordResetMailerTest extends CIUnitTestCase
{
    public function testMailUsesHtmlAndTextAndClearsTransportWithoutDebugOutput(): void
    {
        $client=new class {
            public array $history=[]; public array $draft=[]; public int $clears=0; public bool $failure=false;
            public function setFrom($email,$name) { $this->draft['from']=$email; }
            public function setTo($email) { $this->draft['to']=$email; }
            public function setSubject($subject) { $this->draft['subject']=$subject; }
            public function setMailType($type) { $this->draft['type']=$type; }
            public function setMessage($message) { $this->draft['html']=$message; }
            public function setAltMessage($message) { $this->draft['text']=$message; }
            public function send($clear=true) {
                \PHPUnit\Framework\Assert::assertFalse($clear);
                $this->history[]=$this->draft;
                if($this->failure) throw new \RuntimeException('private-transport-fixture');
                return true;
            }
            public function clear($attachments=false) { $this->clears++; $this->draft=[]; }
            public function printDebugger($parts=[]) { throw new \RuntimeException('No debugger allowed'); }
        };
        $mailer=new class($client) extends PasswordResetMailer {
            private $client;
            public function __construct($client) { $this->client=$client; }
            protected function emailClient() { return $this->client; }
        };
        $this->assertTrue($mailer->sendCode('fixture@example.test','012345'));
        $this->assertSame('fixture@example.test',$client->history[0]['to']);
        $this->assertSame('html',$client->history[0]['type']);
        $this->assertStringContainsString('012345',$client->history[0]['html']);
        $this->assertStringContainsString('012345',$client->history[0]['text']);
        $this->assertSame([],$client->draft);
        $this->assertTrue($mailer->sendChanged('fixture@example.test'));
        $this->assertStringNotContainsString('012345',$client->history[1]['html']);
        $client->failure=true;
        $this->assertFalse($mailer->sendCode('fixture@example.test','654321'));
        $this->assertSame(3,$client->clears);
    }
}
