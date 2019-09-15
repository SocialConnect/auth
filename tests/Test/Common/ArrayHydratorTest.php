<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace Test\Common;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Common\Entity\User;

class ArrayHydratorTest extends AbstractProviderTestCase
{
    public function testHydrationSuccess()
    {
        $hydrator = new ArrayHydrator([
            'firstname' => 'firstname',
            'sex' => static function ($value, User $user) {
                $user->setGender($value);
            },
            'image.picture' => 'pictureURL',
        ]);

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), [
            'firstname' => $expectedFirstName = 'Dima',
            'image' => [
                'picture' => $expectedPictureUrl = 'https://host.com/avatar.jpeg'
            ],
            'sex' => $expectedSex = 'female'
        ]);

        parent::assertEquals($expectedFirstName, $user->firstname);
        parent::assertEquals($expectedPictureUrl, $user->pictureURL);
        parent::assertEquals($expectedSex, $user->getGender());
    }
}
