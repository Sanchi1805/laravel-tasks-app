<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todo;

class TodoController extends Controller
{
    public function index()
    {
        $todos = Todo::where('completed', false)->orderBy('id', 'DESC')->get();
        return view('welcome', ['todos' => $todos]);
    }
    public function fetchAll()
    {
        $todos = Todo::orderBy('id', 'DESC')->get();
        return response()->json(['todos' => $todos]);
    }

    public function store(Request $request)
    {
        // Validate
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $existingTask = Todo::where('name', $validatedData['name'])->first();

        if ($existingTask) {
            // If the task already exists
            return response()->json(['error' => 'Task already exists.'], 409);
        }
        // Create a new Todo
        $todo = Todo::create([
            'name' => $validatedData['name'],
            'completed' => false,
        ]);

        return response()->json($todo);
    }
    public function complete(Request $request, $id)
    {
        $todo = Todo::find($id);
        if ($todo) {
            $todo->completed = $request->completed ? true : false;
            $todo->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    public function delete($id)
    {
        $todo = Todo::find($id);

        if ($todo) {
            $todo->delete();
            return response()->json('success');
        }

        return response()->json('error', 404);
    }

}
