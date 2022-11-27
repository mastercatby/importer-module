<?php

namespace App\Modules\Importer\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Importer\Repositories\ImporterRepository;
use Illuminate\Config\Repository as Config;
use App\Modules\Importer\Http\Requests\ImporterRequest;
use App\Modules\Importer\Models\Importer;
use App\Modules\Importer\Models\ImporterParser;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
//use Illuminate\Http\UploadedFile;
use App;


/**
 * Class ImporterController
 *
 * @package App\Modules\Importer\Http\Controllers
 */
class ImporterController extends Controller
{
    /**
     * Importer repository
     *
     * @var ImporterRepository
     */
    private $importerRepository;

    /**
     * Set repository and apply auth filter
     *
     * @param ImporterRepository $importerRepository
     */
    public function __construct(ImporterRepository $importerRepository)
    {
        //$this->middleware('auth');
        $this->importerRepository = $importerRepository;
    }

    public function checkPermissions($permissions) {
        return true;
    }

    /**
     * Return list of Importer
     *
     * @param Config $config
     *
     * @return Response
     */
    public function index(Config $config)
    {
        $this->checkPermissions(['importer.index']);
        //$onPage = $config->get('system_settings.importer_pagination');
        //$list = $this->importerRepository->paginate($onPage);
        //return response()->json($list);

        $importer = new Importer();
        return view('importer::index', ['list' => $importer->all()]);

    }


    public function importFromFile(Request $request) {

        $msg = '';

        if ($request->has('importfile')) {

            $data = $request->file('importfile')->get();
        
            $parser = new ImporterParser();
        
            if ($parser->loadFromHtml($data) ) {
        
                $parser->storeAll();
                $data = $parser->saveToCsv();

                $importLogger = new Importer();
                $importLogger->run_at               = date('Y-m-d H:i:s');
                $importLogger->entries_processed    = $parser->getEntriesProcessed();
                $importLogger->entries_created      = $parser->getEntriesCreated();
                $importLogger->entries_skipped      = $parser->getEntriesSkipped();
                $importLogger->errors               = $parser->getErrors();
                try {
                    $importLogger->save();
                } catch (\Exception $e) {
                    //echo $e->getMessage();
                }

                $msg = sprintf('%s founded, %s processed, %s created, %s skipped, %s errors',
                    $parser->getEntriesFounded(),
                    $parser->getEntriesProcessed(),
                    $parser->getEntriesCreated(),
                    $parser->getEntriesSkipped(),
                    $parser->getErrors()
                );

                return view('importer::ffres', ['msg' => $msg, 'csv' => htmlentities($data)]);

            } else {
                $msg = 'import error';
            } 

        } else {
            $msg = 'missing uploaded file';
        }
        
        return view('importer::ffres', ['msg' => $msg]);
    }


    /**
     * Display the specified Importer
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $this->checkPermissions(['importer.show']);
        $id = (int) $id;

        return response()->json($this->importerRepository->show($id));
    }

    /**
     * Return module configuration for store action
     *
     * @return Response
     */
    public function create()
    {
        $this->checkPermissions(['importer.store']);
        $rules['fields'] = $this->importerRepository->getRequestRules();

        return response()->json($rules);
    }

    /**
     * Store a newly created Importer in storage.
     *
     * @param ImporterRequest $request
     *
     * @return Response
     */
    public function store(ImporterRequest $request)
    {
        $this->checkPermissions(['importer.store']);
        $model = $this->importerRepository->create($request->all());

        return response()->json(['item' => $model], 201);
    }

    /**
     * Display Importer and module configuration for update action
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $this->checkPermissions(['importer.update']);
        $id = (int) $id;

        return response()->json($this->importerRepository->show($id, true));
    }

    /**
     * Update the specified Importer in storage.
     *
     * @param ImporterRequest $request
     * @param  int $id
     *
     * @return Response
     */
    public function update(ImporterRequest $request, $id)
    {
        $this->checkPermissions(['importer.update']);
        $id = (int) $id;

        $record = $this->importerRepository->updateWithIdAndInput($id,
            $request->all());

        return response()->json(['item' => $record]);
    }

    /**
     * Remove the specified Importer from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $this->checkPermissions(['importer.destroy']);
        //App::abort(404);
        exit;

        /* $id = (int) $id;
        $this->importerRepository->destroy($id); */
    }
}
