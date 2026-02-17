<?php

namespace Akeneo\Pim\Structure\Bundle\Controller\InternalApi;

use Akeneo\Pim\Structure\Component\Factory\GroupTypeFactory;
use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Group type controller
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeController
{
    /** @var GroupTypeRepositoryInterface */
    protected $groupTypeRepo;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var RemoverInterface */
    protected $remover;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var SaverInterface */
    protected $saver;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var UserContext */
    protected $userContext;

    /** @var GroupTypeFactory */
    protected $groupTypeFactory;

    /** @var NormalizerInterface */
    protected $constraintViolationNormalizer;

    /**
     * @param groupTypeFactory             $groupTypeFactory
     */
    public function __construct(
        GroupTypeRepositoryInterface $groupTypeRepo,
        NormalizerInterface $normalizer,
        RemoverInterface $remover,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        ValidatorInterface $validator,
        UserContext $userContext,
        groupTypeFactory $groupTypeFactory,
        NormalizerInterface $constraintViolationNormalizer
    ) {
        $this->groupTypeRepo = $groupTypeRepo;
        $this->normalizer = $normalizer;
        $this->remover = $remover;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
        $this->userContext = $userContext;
        $this->groupTypeFactory = $groupTypeFactory;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
    }

    /**
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_grouptype_index")
     */
    public function indexAction()
    {
        $groupTypes = $this->groupTypeRepo->findAll();

        $data = $this->normalizer->normalize($groupTypes, 'internal_api');

        return new JsonResponse($data);
    }

    /**
     * Get action
     *
     * @param string $identifier
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_grouptype_get")
     */
    public function getAction($identifier)
    {
        $groupType = $this->getGroupTypeOr404($identifier);

        return new JsonResponse(
            $this->normalizer->normalize($groupType, 'internal_api')
        );
    }

    /**
     *
     *
     * @return Response
     * @AclAncestor("pim_enrich_grouptype_edit")
     */
    public function postAction(Request $request, $identifier)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $groupType = $this->getGroupTypeOr404($identifier);

        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new JsonResponse(['message' => 'Invalid json message received'], Response::HTTP_BAD_REQUEST);
        }

        $this->updater->update($groupType, $data);

        $violations = $this->validator->validate($groupType);

        if (0 < $violations->count()) {
            $errors = $this->normalizer->normalize(
                $violations,
                'standard'
            );

            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->saver->save($groupType);

        return new JsonResponse(
            $this->normalizer->normalize(
                $groupType,
                'internal_api',
                $this->userContext->toArray()
            )
        );
    }

    /**
     * Remove action
     *
     * @param string $code
     *
     * @return Response
     *
     * @AclAncestor("pim_enrich_grouptype_remove")
     */
    public function removeAction(Request $request, $code)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'message' => 'An error occurred.',
                    'global' => true,
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $groupType = $this->getGroupTypeOr404($code);

        $this->remover->remove($groupType);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Finds group type by code or throws not found exception
     *
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return GroupTypeInterface
     */
    protected function getGroupTypeOr404($code)
    {
        $groupType = $this->groupTypeRepo->findOneByIdentifier($code);

        if (null === $groupType) {
            throw new NotFoundHttpException(
                sprintf('Group type with code "%s" not found', $code)
            );
        }

        return $groupType;
    }

    /**
     * Creates group type
     *
     *
     * @return Response
     * @AclAncestor("pim_enrich_grouptype_create")
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $groupType = $this->groupTypeFactory->create();
        try {
            $decodedContent = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new JsonResponse(['message' => 'Invalid json message received'], Response::HTTP_BAD_REQUEST);
        }
        $this->updater->update($groupType, $decodedContent);
        $violations = $this->validator->validate($groupType);

        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['groupType' => $groupType]
            );
        }

        if (count($normalizedViolations) > 0) {
            return new JsonResponse(['values' => $normalizedViolations], Response::HTTP_BAD_REQUEST);
        }

        $this->saver->save($groupType);

        return new JsonResponse($this->normalizer->normalize(
            $groupType,
            'internal_api'
        ));
    }
}
