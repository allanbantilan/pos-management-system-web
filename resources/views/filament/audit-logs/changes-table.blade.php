<div class="fi-in-section fi-in-section-default">
    <div class="fi-in-section-header">
        <h3 class="fi-in-section-header-heading">Changes</h3>
    </div>

    <div class="fi-in-section-content">
        <div class="fi-ta-content-ctn">
            <table class="fi-ta-table">
                <thead>
                    <tr class="fi-ta-header-row">
                        <th class="fi-ta-header-cell">Field</th>
                        <th class="fi-ta-header-cell">Old</th>
                        <th class="fi-ta-header-cell">New</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="fi-ta-row">
                            <td class="fi-ta-cell">{{ $row['field'] }}</td>
                            <td class="fi-ta-cell">{{ $row['old'] }}</td>
                            <td class="fi-ta-cell">{{ $row['new'] }}</td>
                        </tr>
                    @empty
                        <tr class="fi-ta-row">
                            <td class="fi-ta-cell" colspan="3">No changes</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
